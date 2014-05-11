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

namespace Thelia\Log\Destination;

use Thelia\Log\TlogDestinationConfig;
use Thelia\Core\Translation\Translator;

class TlogDestinationRotatingFile extends TlogDestinationFile
{
    // Nom des variables de configuration
    // ----------------------------------

    const VAR_MAX_FILE_SIZE_KB = "tlog_destinationfile_max_file_size";
    const MAX_FILE_SIZE_KB_DEFAULT = 1024; // 1 Mb

    public function __construct($maxFileSize = self::MAX_FILE_SIZE_KB_DEFAULT)
    {
        $this->path_defaut = THELIA_ROOT . "log" . DS . self::TLOG_DEFAULT_NAME;

        $this->setConfig(self::VAR_MAX_FILE_SIZE_KB, $maxFileSize, false);

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();

        $file_path = $this->getFilePath();
        $mode = $this->getOpenMode();

        if ($this->fh) @fclose($this->fh);

        if (filesize($file_path) > 1024 * $this->getConfig(self::VAR_MAX_FILE_SIZE_KB, self::MAX_FILE_SIZE_KB_DEFAULT)) {

            $idx = 1;

            do {
                $file_path_bk = "$file_path.$idx";

                $idx++;

            } while (file_exists($file_path_bk));

            @rename($file_path, $file_path_bk);

            @touch($file_path);
            @chmod($file_path, 0666);
        }

        $this->fh = fopen($file_path, $mode);
    }

    public function getTitle()
    {
            return Translator::getInstance()->trans('Rotated Text File');
    }

    public function getDescription()
    {
            return Translator::getInstance()->trans('Store logs into text file, up to a certian size, then a new file is created');
    }

    public function getConfigs()
    {
        $arr = parent::getConfigs();

        $arr[] =
             new TlogDestinationConfig(
                self::VAR_MAX_FILE_SIZE_KB,
                'Maximum log file size, in Kb',
                'When this size if exeeded, a backup copy of the file is made, and a new log file is opened. As the file size check is performed only at the beginning of a request, the file size may be bigger thant this limit. Note: 1 Mb = 1024 Kb',
                self::MAX_FILE_SIZE_KB_DEFAULT,
                TlogDestinationConfig::TYPE_TEXTFIELD
        );

        return $arr;
    }
}
