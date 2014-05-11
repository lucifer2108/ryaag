<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/
namespace Thelia\Action;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Thelia\Core\Event\Document\DocumentCreateOrUpdateEvent;
use Thelia\Core\Event\Document\DocumentDeleteEvent;
use Thelia\Core\Event\Document\DocumentEvent;
use Thelia\Core\Event\UpdateFilePositionEvent;
use Thelia\Exception\ImageException;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\FileManager;
use Thelia\Tools\URL;

use Thelia\Exception\DocumentException;
use Thelia\Core\Event\TheliaEvents;

/**
 *
 * Document management actions. This class handles document processing an caching.
 *
 * Basically, documents are stored outside the web space (by default in local/media/documents),
 * and cached in the web space (by default in web/local/documents).
 *
 * In the documents caches directory, a subdirectory for documents categories (eg. product, category, folder, etc.) is
 * automatically created, and the cached document is created here. Plugin may use their own subdirectory as required.
 *
 * The cached document name contains a hash of the processing options, and the original (normalized) name of the document.
 *
 * A copy (or symbolic link, by default) of the original document is always created in the cache, so that the full
 * resolution document is always available.
 *
 * If a problem occurs, an DocumentException may be thrown.
 *
 * @package Thelia\Action
 * @author Franck Allimant <franck@cqfdev.fr>
 *
 */
class Document extends BaseCachedFile implements EventSubscriberInterface
{
    /**
     * @return string root of the document cache directory in web space
     */
    protected function getCacheDirFromWebRoot()
    {
        return ConfigQuery::read('document_cache_dir_from_web_root', 'cache');
    }

    /**
     * Process document and write the result in the document cache.
     *
     * When the original document is required, create either a symbolic link with the
     * original document in the cache dir, or copy it in the cache dir if it's not already done.
     *
     * This method updates the cache_file_path and file_url attributes of the event
     *
     * @param DocumentEvent $event Event
     *
     * @throws \Thelia\Exception\DocumentException
     * @throws \InvalidArgumentException           , DocumentException
     */
    public function processDocument(DocumentEvent $event)
    {
        $subdir      = $event->getCacheSubdirectory();
        $sourceFile = $event->getSourceFilepath();

        if (null == $subdir || null == $sourceFile) {
            throw new \InvalidArgumentException("Cache sub-directory and source file path cannot be null");
        }

        $originalDocumentPathInCache = $this->getCacheFilePath($subdir, $sourceFile, true);

        if (! file_exists($originalDocumentPathInCache)) {

            if (! file_exists($sourceFile)) {
                throw new DocumentException(sprintf("Source document file %s does not exists.", $sourceFile));
            }

            $mode = ConfigQuery::read('original_document_delivery_mode', 'symlink');

            if ($mode == 'symlink') {
                if (false == symlink($sourceFile, $originalDocumentPathInCache)) {
                     throw new DocumentException(sprintf("Failed to create symbolic link for %s in %s document cache directory", basename($sourceFile), $subdir));
                }
            } else {
                // mode = 'copy'
                if (false == @copy($sourceFile, $originalDocumentPathInCache)) {
                    throw new DocumentException(sprintf("Failed to copy %s in %s document cache directory", basename($sourceFile), $subdir));
                }
            }
        }

        // Compute the document URL
        $documentUrl = $this->getCacheFileURL($subdir, basename($originalDocumentPathInCache));

        // Update the event with file path and file URL
        $event->setDocumentPath($documentUrl);
        $event->setDocumentUrl(URL::getInstance()->absoluteUrl($documentUrl, null, URL::PATH_TO_FILE));
    }

    /**
     * Take care of saving document in the database and file storage
     *
     * @param \Thelia\Core\Event\Document\DocumentCreateOrUpdateEvent $event Document event
     *
     * @throws \Thelia\Exception\ImageException
     * @todo refactor make all documents using propel inheritance and factorise image behaviour into one single clean action
     */
    public function saveDocument(DocumentCreateOrUpdateEvent $event)
    {
        $fileManager = new FileManager();
        $model = $event->getModelDocument();

        $nbModifiedLines = $model->save();

        $event->setModelDocument($model);

        if (!$nbModifiedLines) {
            throw new ImageException(
                sprintf(
                    'Document "%s" with parent id %s (%s) failed to be saved',
                    $event->getParentName(),
                    $event->getParentId(),
                    $event->getDocumentType()
                )
            );
        }

        $newUploadedFile = $fileManager->copyUploadedFile($event->getParentId(), $event->getDocumentType(), $event->getModelDocument(), $event->getUploadedFile(), FileManager::FILE_TYPE_DOCUMENTS);
        $event->setUploadedFile($newUploadedFile);
    }

    /**
     * Take care of updating document in the database and file storage
     *
     * @param \Thelia\Core\Event\Document\DocumentCreateOrUpdateEvent $event Document event
     *
     * @throws \Thelia\Exception\ImageException
     * @todo refactor make all documents using propel inheritance and factorise image behaviour into one single clean action
     */
    public function updateDocument(DocumentCreateOrUpdateEvent $event)
    {
        if (null !== $event->getUploadedFile()) {
            $event->getModelDocument()->setTitle($event->getUploadedFile()->getClientOriginalName());
        }

        $fileManager = new FileManager();
        // Copy and save file
        if ($event->getUploadedFile()) {
            // Remove old picture file from file storage
            $url = $fileManager->getUploadDir($event->getDocumentType(), FileManager::FILE_TYPE_DOCUMENTS) . '/' . $event->getOldModelDocument()->getFile();
            unlink(str_replace('..', '', $url));

            $newUploadedFile = $fileManager->copyUploadedFile($event->getParentId(), $event->getDocumentType(), $event->getModelDocument(), $event->getUploadedFile(), FileManager::FILE_TYPE_DOCUMENTS);
            $event->setUploadedFile($newUploadedFile);
        }

        // Update document modifications
        $event->getModelDocument()->save();
        $event->setModelDocument($event->getModelDocument());
    }

    public function updatePosition(UpdateFilePositionEvent $event)
    {
        $this->genericUpdatePosition($event->getQuery(), $event);
    }

    /**
     * Take care of deleting document in the database and file storage
     *
     * @param \Thelia\Core\Event\Document\DocumentDeleteEvent $event Image event
     *
     * @throws \Exception
     * @todo refactor make all documents using propel inheritance and factorise image behaviour into one single clean action
     */
    public function deleteDocument(DocumentDeleteEvent $event)
    {
        $fileManager = new FileManager();

        $fileManager->deleteFile($event->getDocumentToDelete(), $event->getDocumentType(), FileManager::FILE_TYPE_DOCUMENTS);
    }

    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::DOCUMENT_PROCESS => array("processDocument", 128),
            TheliaEvents::DOCUMENT_CLEAR_CACHE => array("clearCache", 128),
            TheliaEvents::DOCUMENT_DELETE => array("deleteDocument", 128),
            TheliaEvents::DOCUMENT_SAVE => array("saveDocument", 128),
            TheliaEvents::DOCUMENT_UPDATE => array("updateDocument", 128),
            TheliaEvents::DOCUMENT_UPDATE_POSITION => array("updatePosition", 128),
        );
    }
}
