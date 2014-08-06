<?php
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thelia\Condition\ConditionFactory;
use Thelia\Condition\Implementation\MatchForEveryone;
use Thelia\Condition\Implementation\MatchForTotalAmount;
use Thelia\Condition\Implementation\MatchForXArticles;
use Thelia\Condition\Operators;
use Thelia\Coupon\FacadeInterface;
use Thelia\Condition\ConditionCollection;
use Thelia\Coupon\Type\RemoveXAmount;
use Thelia\Coupon\Type\RemoveXPercent;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderAddress;

if (php_sapi_name() != 'cli') {
    throw new \Exception('this script can only be launched with cli sapi');
}

require __DIR__ . '/../core/bootstrap.php';

$thelia = new Thelia\Core\Thelia("dev", true);
$thelia->boot();

$faker = Faker\Factory::create();

$con = \Propel\Runtime\Propel::getConnection(
    Thelia\Model\Map\ProductTableMap::DATABASE_NAME
);
$con->beginTransaction();

// Intialize URL management
$url = new Thelia\Tools\URL();

$currency = \Thelia\Model\CurrencyQuery::create()->filterByCode('EUR')->findOne();

try {
    $stmt = $con->prepare("SET foreign_key_checks = 0");
    $stmt->execute();

    echo "Clearing tables\n";

    $productAssociatedContent = Thelia\Model\ProductAssociatedContentQuery::create()
        ->find();
    $productAssociatedContent->delete();

    $categoryAssociatedContent = Thelia\Model\CategoryAssociatedContentQuery::create()
        ->find();
    $categoryAssociatedContent->delete();

    $featureProduct = Thelia\Model\FeatureProductQuery::create()
        ->find();
    $featureProduct->delete();

    $attributeCombination = Thelia\Model\AttributeCombinationQuery::create()
        ->find();
    $attributeCombination->delete();

    $feature = Thelia\Model\FeatureQuery::create()
        ->find();
    $feature->delete();

    $feature = Thelia\Model\FeatureI18nQuery::create()
        ->find();
    $feature->delete();

    $featureAv = Thelia\Model\FeatureAvQuery::create()
        ->find();
    $featureAv->delete();

    $featureAv = Thelia\Model\FeatureAvI18nQuery::create()
        ->find();
    $featureAv->delete();

    $attribute = Thelia\Model\AttributeQuery::create()
        ->find();
    $attribute->delete();

    $attribute = Thelia\Model\AttributeI18nQuery::create()
        ->find();
    $attribute->delete();

    $attributeAv = Thelia\Model\AttributeAvQuery::create()
        ->find();
    $attributeAv->delete();

    $attributeAv = Thelia\Model\AttributeAvI18nQuery::create()
        ->find();
    $attributeAv->delete();

    $category = Thelia\Model\CategoryQuery::create()
        ->find();
    $category->delete();

    $category = Thelia\Model\CategoryI18nQuery::create()
        ->find();
    $category->delete();

    $product = Thelia\Model\ProductQuery::create()
        ->find();
    $product->delete();

    $product = Thelia\Model\ProductI18nQuery::create()
        ->find();
    $product->delete();

    $customer = Thelia\Model\CustomerQuery::create()
        ->find();
    $customer->delete();

    $admin = Thelia\Model\AdminQuery::create()
        ->find();
    $admin->delete();

    $folder = Thelia\Model\FolderQuery::create()
        ->find();
    $folder->delete();

    $folder = Thelia\Model\FolderI18nQuery::create()
        ->find();
    $folder->delete();

    $content = Thelia\Model\ContentQuery::create()
        ->find();
    $content->delete();

    $content = Thelia\Model\ContentI18nQuery::create()
        ->find();
    $content->delete();

    $accessory = Thelia\Model\AccessoryQuery::create()
        ->find();
    $accessory->delete();

    $stock = \Thelia\Model\ProductSaleElementsQuery::create()
        ->find();
    $stock->delete();

    $productPrice = \Thelia\Model\ProductPriceQuery::create()
        ->find();
    $productPrice->delete();

    $brand = Thelia\Model\BrandQuery::create()
        ->find();
    $brand->delete();

    $brand = Thelia\Model\BrandI18nQuery::create()
        ->find();
    $brand->delete();

    \Thelia\Model\ProductImageQuery::create()->find()->delete();
    \Thelia\Model\CategoryImageQuery::create()->find()->delete();
    \Thelia\Model\FolderImageQuery::create()->find()->delete();
    \Thelia\Model\ContentImageQuery::create()->find()->delete();
    \Thelia\Model\BrandImageQuery::create()->find()->delete();

    \Thelia\Model\ProductDocumentQuery::create()->find()->delete();
    \Thelia\Model\CategoryDocumentQuery::create()->find()->delete();
    \Thelia\Model\FolderDocumentQuery::create()->find()->delete();
    \Thelia\Model\ContentDocumentQuery::create()->find()->delete();
    \Thelia\Model\BrandDocumentQuery::create()->find()->delete();

    \Thelia\Model\CouponQuery::create()->find()->delete();
    \Thelia\Model\OrderQuery::create()->find()->delete();

    $stmt = $con->prepare("SET foreign_key_checks = 1");

    $stmt->execute();

    echo "Creating customers\n";

    //customer
    $customer = new Thelia\Model\Customer();
    $customer->createOrUpdate(
        1,
        "thelia",
        "thelia",
        "5 rue rochon",
        "",
        "",
        "0102030405",
        "0601020304",
        "63000",
        "clermont-ferrand",
        64,
        "test@thelia.net",
        "azerty"
    );
    for ($j = 0; $j <= 3; $j++) {
        $address = new Thelia\Model\Address();
        $address->setLabel($faker->text(20))
            ->setTitleId(rand(1,3))
            ->setFirstname($faker->firstname)
            ->setLastname($faker->lastname)
            ->setAddress1($faker->streetAddress)
            ->setAddress2($faker->streetAddress)
            ->setAddress3($faker->streetAddress)
            ->setCellphone($faker->phoneNumber)
            ->setPhone($faker->phoneNumber)
            ->setZipcode($faker->postcode)
            ->setCity($faker->city)
            ->setCountryId(64)
            ->setCustomer($customer)
            ->save()
        ;
    }

    for ($i=0; $i<3; $i++) {
        $admin = new Thelia\Model\Admin();
        $admin
            ->setFirstname($faker->firstname)
            ->setLastname($faker->lastname)
            ->setLogin($faker->firstname)
            ->setPassword('azerty')
            ->setLocale('en_US')
            ->save();
    }

    for ($i = 0; $i < 50; $i++) {
        $customer = new Thelia\Model\Customer();
        $customer->createOrUpdate(
            rand(1,3),
            $faker->firstname,
            $faker->lastname,
            $faker->streetAddress,
            $faker->streetAddress,
            $faker->streetAddress,
            $faker->phoneNumber,
            $faker->phoneNumber,
            $faker->postcode,
            $faker->city,
            64,
            $faker->email,
            "azerty".$i
        );

        for ($j = 0; $j <= 3; $j++) {
            $address = new Thelia\Model\Address();
            $address->setLabel($faker->text(20))
                ->setTitleId(rand(1,3))
                ->setFirstname($faker->firstname)
                ->setLastname($faker->lastname)
                ->setAddress1($faker->streetAddress)
                ->setAddress2($faker->streetAddress)
                ->setAddress3($faker->streetAddress)
                ->setCellphone($faker->phoneNumber)
                ->setPhone($faker->phoneNumber)
                ->setZipcode($faker->postcode)
                ->setCity($faker->city)
                ->setCountryId(64)
                ->setCustomer($customer)
                ->save()
            ;

        }
    }

    echo "Creating features\n";

    //features and features_av
    $featureList = array();
    for ($i=0; $i<4; $i++) {
        $feature = new Thelia\Model\Feature();
        $feature->setVisible(1);
        $feature->setPosition($i);
        setI18n($faker, $feature);

        $feature->save();
        $featureId = $feature->getId();
        $featureList[$featureId] = array();

        for ($j=0; $j<rand(-2, 5); $j++) { //let a chance for no av
            $featureAv = new Thelia\Model\FeatureAv();
            $featureAv->setFeature($feature);
            $featureAv->setPosition($j);
            setI18n($faker, $featureAv);

            $featureAv->save();
            $featureList[$featureId][] = $featureAv->getId();
        }
    }

    echo "Creating attributes\n";

    //attributes and attributes_av
    $attributeList = array();
    for ($i=0; $i<4; $i++) {
        $attribute = new Thelia\Model\Attribute();
        $attribute->setPosition($i);
        setI18n($faker, $attribute);

        $attribute->save();
        $attributeId = $attribute->getId();
        $attributeList[$attributeId] = array();

        for ($j=0; $j<rand(1, 5); $j++) {
            $attributeAv = new Thelia\Model\AttributeAv();
            $attributeAv->setAttribute($attribute);
            $attributeAv->setPosition($j);
            setI18n($faker, $attributeAv);

            $attributeAv->save();
            $attributeList[$attributeId][] = $attributeAv->getId();
        }
    }

    echo "Creating templates\n";

    $template = new Thelia\Model\Template();
    setI18n($faker, $template, array("Name" => 20));
    $template->save();

    foreach ($attributeList as $attributeId => $attributeAvId) {
        $at = new Thelia\Model\AttributeTemplate();

        $at
            ->setTemplate($template)
            ->setAttributeId($attributeId)
            ->save();
    }

    foreach ($featureList as $featureId => $featureAvId) {
        $ft = new Thelia\Model\FeatureTemplate();

        $ft
        ->setTemplate($template)
        ->setFeatureId($featureId)
        ->save();
    }

    echo "Creating folders and contents\n";

    //folders and contents
    $contentIdList = array();
    for ($i=0; $i<4; $i++) {
        $folder = new Thelia\Model\Folder();
        $folder->setParent(0);
        $folder->setVisible(1);
        $folder->setPosition($i+1);
        setI18n($faker, $folder);

        $folder->save();

        $image = new \Thelia\Model\FolderImage();
        $image->setFolderId($folder->getId());
        generate_image($image, 'folder', $folder->getId());

        $document = new \Thelia\Model\FolderDocument();
        $document->setFolderId($folder->getId());
        generate_document($document, 'folder', $folder->getId());

        for ($j=0; $j<3; $j++) {
            $subfolder = new Thelia\Model\Folder();
            $subfolder->setParent($folder->getId());
            $subfolder->setVisible(1);
            $subfolder->setPosition($j+1);
            setI18n($faker, $subfolder);

            $subfolder->save();

            $image = new \Thelia\Model\FolderImage();
            $image->setFolderId($subfolder->getId());
            generate_image($image, 'folder', $subfolder->getId());

            $document = new \Thelia\Model\FolderDocument();
            $document->setFolderId($folder->getId());
            generate_document($document, 'folder', $subfolder->getId());

            for ($k=0; $k<4; $k++) {
                $content = new Thelia\Model\Content();
                $content->addFolder($subfolder);

                $contentFolders = $content->getContentFolders();
                $collection = new \Propel\Runtime\Collection\Collection();
                $collection->prepend($contentFolders[0]->setDefaultFolder(1));
                $content->setContentFolders($collection);

                $content->setVisible(1);
                $content->setPosition($k+1);
                setI18n($faker, $content);

                $content->save();
                $contentId = $content->getId();
                $contentIdList[] = $contentId;

                $image = new \Thelia\Model\ContentImage();
                $image->setContentId($contentId);
                generate_image($image, 'content', $contentId);

                $document = new \Thelia\Model\ContentDocument();
                $document->setContentId($contentId);
                generate_document($document, 'content', $contentId);

            }
        }
    }

    echo "Creating brands\n";

    $brandIdList = [];

    for ($k=0; $k<10; $k++) {
        $brand = new Thelia\Model\Brand();

        $brand->setVisible(1);
        $brand->setPosition($k+1);
        setI18n($faker, $brand);

        $brand->save();
        $brandId = $brand->getId();
        $brandIdList[] = $brandId;

        $image = new \Thelia\Model\BrandImage();
        $image->setBrandId($brandId);
        generate_image($image, 'brand', $brandId);

        $document = new \Thelia\Model\BrandDocument();
        $document->setBrandId($brandId);
        generate_document($document, 'brand', $brandId);
    }

    echo "Creating categories and products\n";

    //categories and products
    $productIdList = array();
    $categoryIdList = array();
    for ($i=1; $i<20; $i++) {
        $category = createCategory($faker, 0, $i, $categoryIdList, $contentIdList);

        for ($j=1; $j<rand(0, 20); $j++) {
            $subcategory = createCategory($faker, $category->getId(), $j, $categoryIdList, $contentIdList);

            for ($k=0; $k<rand(0, 20); $k++) {
                createProduct($faker, $subcategory, $k, $template, $brandIdList, $productIdList);
            }
        }

        for ($k=1; $k<rand(1, 50); $k++) {
            createProduct($faker, $category, $k, $template, $brandIdList, $productIdList);
        }
    }

    foreach ($productIdList as $productId) {
        //add random accessories - or not
        $alreadyPicked = array();
        for ($i=1; $i<rand(0, 4); $i++) {
            $accessory = new Thelia\Model\Accessory();
            do {
                $pick = array_rand($productIdList, 1);
            } while (in_array($pick, $alreadyPicked));

            $alreadyPicked[] = $pick;

            $accessory->setAccessory($productIdList[$pick])
                ->setProductId($productId)
                ->setPosition($i)
                ->save();
        }

        //add random associated content
        $alreadyPicked = array();
        for ($i=1; $i<rand(0, 3); $i++) {
            $productAssociatedContent = new Thelia\Model\ProductAssociatedContent();
            do {
                $pick = array_rand($contentIdList, 1);
                \Thelia\Log\Tlog::getInstance()->debug("pick : $pick");
            } while (in_array($pick, $alreadyPicked));

            $alreadyPicked[] = $pick;

            $productAssociatedContent->setContentId($contentIdList[$pick])
                ->setProductId($productId)
                ->setPosition($i)
                ->save();
        }

        //associate PSE and stocks to products
        for ($i=0; $i<rand(1,7); $i++) {
            $stock = new \Thelia\Model\ProductSaleElements();
            $stock->setProductId($productId);
            $stock->setRef($productId . '_' . $i . '_' . $faker->randomNumber(8));
            $stock->setQuantity($faker->randomNumber(1,50));
            $stock->setPromo($faker->randomNumber(0,1));
            $stock->setNewness($faker->randomNumber(0,1));
            $stock->setWeight($faker->randomFloat(2, 1, 5));
            $stock->setIsDefault($i == 0);
            $stock->setEanCode(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 13));
            $stock->save();

            $productPrice = new \Thelia\Model\ProductPrice();
            $productPrice->setProductSaleElements($stock);
            $productPrice->setCurrency($currency);
            $productPrice->setPrice($faker->randomFloat(2, 20, 250));
            $productPrice->setPromoPrice($faker->randomFloat(2, 20, 250));
            $productPrice->save();

            //associate attributes - or not - to PSE

            $alreadyPicked = array();
            for ($i=0; $i<rand(-2,count($attributeList)); $i++) {
                $featureProduct = new Thelia\Model\AttributeCombination();
                do {
                    $pick = array_rand($attributeList, 1);
                } while (in_array($pick, $alreadyPicked));

                $alreadyPicked[] = $pick;

                $featureProduct->setAttributeId($pick)
                    ->setAttributeAvId($attributeList[$pick][array_rand($attributeList[$pick], 1)])
                    ->setProductSaleElements($stock)
                    ->save();
            }
        }

        //associate features to products
        foreach ($featureList as $featureId => $featureAvId) {
            $featureProduct = new Thelia\Model\FeatureProduct();
            $featureProduct->setProductId($productId)
                ->setFeatureId($featureId);

            if (count($featureAvId) > 0) { //got some av
                $featureProduct->setFeatureAvId(
                    $featureAvId[array_rand($featureAvId, 1)]
                );
            } else { //no av
                $featureProduct->setFreeTextValue($faker->text(10));
            }

            $featureProduct->save();
        }
    }

    echo "Creating orders\n";

    $colissimo_id = ModuleQuery::create()->
        filterByCode("Colissimo")
        ->findOne()
        ->getId();

    $cheque_id = ModuleQuery::create()
        ->filterByCode("Cheque")
        ->findOne()
        ->getId();

    for ($i=0; $i < 50; ++$i) {
        $placedOrder = new \Thelia\Model\Order();

        $deliveryOrderAddress = new OrderAddress();
        $deliveryOrderAddress
            ->setCustomerTitleId(mt_rand(1,3))
            ->setCompany($faker->text(15))
            ->setFirstname($faker->firstname)
            ->setLastname($faker->lastname)
            ->setAddress1($faker->streetAddress)
            ->setAddress2($faker->streetAddress)
            ->setAddress3($faker->streetAddress)
            ->setPhone($faker->phoneNumber)
            ->setZipcode($faker->postcode)
            ->setCity($faker->city)
            ->setCountryId(64)
        ->save($con)
        ;

        $invoiceOrderAddress = new OrderAddress();
        $invoiceOrderAddress
            ->setCustomerTitleId(mt_rand(1,3))
            ->setCompany($faker->text(15))
            ->setFirstname($faker->firstname)
            ->setLastname($faker->lastname)
            ->setAddress1($faker->streetAddress)
            ->setAddress2($faker->streetAddress)
            ->setAddress3($faker->streetAddress)
            ->setPhone($faker->phoneNumber)
            ->setZipcode($faker->postcode)
            ->setCity($faker->city)
            ->setCountryId(64)
        ->save($con)
        ;

        $placedOrder
            ->setDeliveryOrderAddressId($deliveryOrderAddress->getId())
            ->setInvoiceOrderAddressId($invoiceOrderAddress->getId())
            ->setDeliveryModuleId($colissimo_id)
            ->setPaymentModuleId($cheque_id)
            ->setStatusId(mt_rand(1, 5))
            ->setCurrency(
                \Thelia\Model\CurrencyQuery::create()
                    ->addAscendingOrderByColumn('RAND()')
                    ->findOne()
            )
            ->setCustomer(
                \Thelia\Model\CustomerQuery::create()
                    ->addAscendingOrderByColumn('RAND()')
                    ->findOne()
            )
            ->setDiscount(mt_rand(0, 10))
            ->setLang(
                \Thelia\Model\LangQuery::create()
                    ->addAscendingOrderByColumn('RAND()')
                    ->findOne()
            )
            ->setPostage(mt_rand(1, 50))
        ;

        $placedOrder->save($con);

        for ($j=0; $j < mt_rand(1, 10); ++$j) {
            $pse = \Thelia\Model\ProductSaleElementsQuery::create()
                ->addAscendingOrderByColumn('RAND()')
                ->findOne();

            $product = $pse->getProduct();

            $orderProduct = new \Thelia\Model\OrderProduct();

            $orderProduct
                ->setOrderId($placedOrder->getId())
                ->setProductRef($product->getRef())
                ->setProductSaleElementsRef($pse->getRef())
                ->setTitle($product->getTitle())
                ->setChapo($product->getChapo())
                ->setDescription($product->getDescription())
                ->setPostscriptum($product->getPostscriptum())
                ->setQuantity(mt_rand(1, 10))
                ->setPrice($price=mt_rand(1, 100))
                ->setPromoPrice(mt_rand(1, $price))
                ->setWasNew($pse->getNewness())
                ->setWasInPromo(rand(0,1) == 1)
                ->setWeight($pse->getWeight())
                ->setTaxRuleTitle($faker->text(20))
                ->setTaxRuleDescription($faker->text(50))
                ->setEanCode($pse->getEanCode())
            ->save($con);
        }

    }

    echo "Generating coupons fixtures\n";

    generateCouponFixtures($thelia);

    $con->commit();

    echo "Successfully terminated.\n";

} catch (Exception $e) {
    echo "error : ".$e->getMessage()."\n";
    echo "Cause: ".$e->getPrevious()->getMessage()."\n";
    echo $e->getTraceAsString();

    $con->rollBack();
}

function createProduct($faker, Thelia\Model\Category $category, $position, $template, $brandIdList, &$productIdList)
{
    $product = new Thelia\Model\Product();
    $product->setRef($category->getId() . '_' . $position . '_' . $faker->randomNumber(8));
    $product->addCategory($category);
    $product->setVisible(1);
    $productCategories = $product->getProductCategories();
    $collection = new \Propel\Runtime\Collection\Collection();
    $collection->prepend($productCategories[0]->setDefaultCategory(1));
    $product->setProductCategories($collection);
    $product->setVisible(1);
    $product->setPosition($position);
    $product->setTaxRuleId(1);
    $product->setTemplate($template);
    $product->setBrandId($brandIdList[array_rand($brandIdList, 1)]);

    setI18n($faker, $product);

    $product->save();
    $productId = $product->getId();
    $productIdList[] = $productId;

    $image = new \Thelia\Model\ProductImage();
    $image->setProductId($productId);
    generate_image($image, 'product', $productId);

    $document = new \Thelia\Model\ProductDocument();
    $document->setProductId($productId);
    generate_document($document, 'product', $productId);

    return $product;
}

function createCategory($faker, $parent, $position, &$categoryIdList, $contentIdList)
{
    $category = new Thelia\Model\Category();
    $category->setParent($parent);
    $category->setVisible(1);
    $category->setPosition($position);
    setI18n($faker, $category);

    $category->save();
    $categoryId = $category->getId();
    $categoryIdList[] = $categoryId;

    //add random associated content
    $alreadyPicked = array();
    for ($i=1; $i<rand(0, 3); $i++) {
        $categoryAssociatedContent = new Thelia\Model\CategoryAssociatedContent();
        do {
            $pick = array_rand($contentIdList, 1);
        } while (in_array($pick, $alreadyPicked));

        $alreadyPicked[] = $pick;

        $categoryAssociatedContent->setContentId($contentIdList[$pick])
            ->setCategoryId($categoryId)
            ->setPosition($i)
            ->save();
    }

    $image = new \Thelia\Model\CategoryImage();
    $image->setCategoryId($categoryId);
    generate_image($image, 'category', $categoryId);

    $document = new \Thelia\Model\CategoryDocument();
    $document->setCategoryId($categoryId);
    generate_document($document, 'category', $categoryId);

    return $category;
}

function generate_image($image, $typeobj, $id)
{
    global $faker;

    $image
        ->setTitle($faker->text(20))
        ->setDescription($faker->text(250))
        ->setChapo($faker->text(40))
        ->setPostscriptum($faker->text(40))
        ->setFile(sprintf("sample-image-%s.png", $id))
        ->save()
    ;

    // Generate images
    $imagine = new Imagine\Gd\Imagine();
    $image   = $imagine->create(new Imagine\Image\Box(320,240), new Imagine\Image\Color('#E9730F'));

    $white = new Imagine\Image\Color('#FFF');

    $font = $imagine->font(__DIR__.'/faker-assets/FreeSans.ttf', 14, $white);

    $tbox = $font->box("THELIA");
    $image->draw()->text("THELIA", $font, new Imagine\Image\Point((320 - $tbox->getWidth()) / 2, 30));

    $str = sprintf("%s sample image", ucfirst($typeobj));
    $tbox = $font->box($str);
    $image->draw()->text($str, $font, new Imagine\Image\Point((320 - $tbox->getWidth()) / 2, 80));

    $font = $imagine->font(__DIR__.'/faker-assets/FreeSans.ttf', 18, $white);

    $str = sprintf("%s ID %d", strtoupper($typeobj), $id);
    $tbox = $font->box($str);
    $image->draw()->text($str, $font, new Imagine\Image\Point((320 - $tbox->getWidth()) / 2, 180));

    $image->draw()
        ->line(new Imagine\Image\Point(0, 0), new Imagine\Image\Point(319, 0), $white)
        ->line(new Imagine\Image\Point(319, 0), new Imagine\Image\Point(319, 239), $white)
        ->line(new Imagine\Image\Point(319, 239), new Imagine\Image\Point(0,239), $white)
        ->line(new Imagine\Image\Point(0, 239), new Imagine\Image\Point(0, 0), $white)
    ;

    $image_file = sprintf("%s/../local/media/images/%s/sample-image-%s.png", __DIR__, $typeobj, $id);

    if (! is_dir(dirname($image_file))) mkdir(dirname($image_file), 0777, true);

    $image->save($image_file);
}

function generate_document($document, $typeobj, $id)
{
    global $faker;

    $document
    ->setTitle($faker->text(20))
    ->setDescription($faker->text(250))
    ->setChapo($faker->text(40))
    ->setPostscriptum($faker->text(40))
    ->setFile(sprintf("sample-document-%s.txt", $id))
    ->save()
    ;

    $document_file = sprintf("%s/../local/media/documents/%s/sample-document-%s.txt", __DIR__, $typeobj, $id);

    if (! is_dir(dirname($document_file))) mkdir(dirname($document_file), 0777, true);

    file_put_contents($document_file, $faker->text(256));
}

function setI18n($faker, &$object, $fields = array('Title' => 20, 'Description' => 50) )
{
    $localeList = $localeList = array('fr_FR', 'en_US', 'es_ES', 'it_IT');

    foreach ($localeList as $locale) {
        $object->setLocale($locale);

        foreach ($fields as $name => $length) {
            $func = "set".ucfirst(strtolower($name));

            $object->$func($locale . ' : ' . $faker->text($length));
        }
    }
}
/**
 * Generate Coupon fixtures
 */
function generateCouponFixtures(\Thelia\Core\Thelia $thelia)
{
    /** @var $container ContainerInterface Service Container */
    $container = $thelia->getContainer();
    /** @var FacadeInterface $adapter */
    $adapter = $container->get('thelia.facade');

    // Coupons
    $coupon1 = new Thelia\Model\Coupon();
    $coupon1->setCode('XMAS');
    $coupon1->setType('thelia.coupon.type.remove_x_amount');
    $coupon1->setTitle('Christmas coupon');
    $coupon1->setShortDescription('Coupon for Christmas removing 10€ if your total checkout is more than 40€');
    $coupon1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at luctus tellus. Integer turpis mauris, aliquet vitae risus tristique, pellentesque vestibulum urna. Vestibulum sodales laoreet lectus dictum suscipit. Praesent vulputate, sem id varius condimentum, quam magna tempor elit, quis venenatis ligula nulla eget libero. Cras egestas euismod tellus, id pharetra leo suscipit quis. Donec lacinia ac lacus et ultricies. Nunc in porttitor neque. Proin at quam congue, consectetur orci sed, congue nulla. Nulla eleifend nunc ligula, nec pharetra elit tempus quis. Vivamus vel mauris sed est dictum blandit. Maecenas blandit dapibus velit ut sollicitudin. In in euismod mauris, consequat viverra magna. Cras velit velit, sollicitudin commodo tortor gravida, tempus varius nulla.

Donec rhoncus leo mauris, id porttitor ante luctus tempus. Curabitur quis augue feugiat, ullamcorper mauris ac, interdum mi. Quisque aliquam lorem vitae felis lobortis, id interdum turpis mattis. Vestibulum diam massa, ornare congue blandit quis, facilisis at nisl. In tortor metus, venenatis non arcu nec, sollicitudin ornare nisl. Nunc erat risus, varius nec urna at, iaculis lacinia elit. Aenean ut felis tempus, tincidunt odio non, sagittis nisl. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec vitae hendrerit elit. Nunc sit amet gravida risus, euismod lobortis massa. Nam a erat mauris. Nam a malesuada lorem. Nulla id accumsan dolor, sed rhoncus tellus. Quisque dictum felis sed leo auctor, at volutpat lectus viverra. Morbi rutrum, est ac aliquam imperdiet, nibh sem sagittis justo, ac mattis magna lacus eu nulla.

Duis interdum lectus nulla, nec pellentesque sapien condimentum at. Suspendisse potenti. Sed eu purus tellus. Nunc quis rhoncus metus. Fusce vitae tellus enim. Interdum et malesuada fames ac ante ipsum primis in faucibus. Etiam tempor porttitor erat vitae iaculis. Sed est elit, consequat non ornare vitae, vehicula eget lectus. Etiam consequat sapien mauris, eget consectetur magna imperdiet eget. Nunc sollicitudin luctus velit, in commodo nulla adipiscing fermentum. Fusce nisi sapien, posuere vitae metus sit amet, facilisis sollicitudin dui. Fusce ultricies auctor enim sit amet iaculis. Morbi at vestibulum enim, eget adipiscing eros.

Praesent ligula lorem, faucibus ut metus quis, fermentum iaculis erat. Pellentesque elit erat, lacinia sed semper ac, sagittis vel elit. Nam eu convallis est. Curabitur rhoncus odio vitae consectetur pellentesque. Nam vitae arcu nec ante scelerisque dignissim vel nec neque. Suspendisse augue nulla, mollis eget dui et, tempor facilisis erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac diam ipsum. Donec convallis dui ultricies velit auctor, non lobortis nulla ultrices. Morbi vitae dignissim ante, sit amet lobortis tortor. Nunc dapibus condimentum augue, in molestie neque congue non.

Sed facilisis pellentesque nisl, eu tincidunt erat scelerisque a. Nullam malesuada tortor vel erat volutpat tincidunt. In vehicula diam est, a convallis eros scelerisque ut. Donec aliquet venenatis iaculis. Ut a arcu gravida, placerat dui eu, iaculis nisl. Quisque adipiscing orci sit amet dui dignissim lacinia. Sed vulputate lorem non dolor adipiscing ornare. Morbi ornare id nisl id aliquam. Ut fringilla elit ante, nec lacinia enim fermentum sit amet. Aenean rutrum lorem eu convallis pharetra. Cras malesuada varius metus, vitae gravida velit. Nam a varius ipsum, ac commodo dolor. Phasellus nec elementum elit. Etiam vel adipiscing leo.');
    $coupon1->setEffects(array(
        RemoveXAmount::AMOUNT_FIELD_NAME => 10.00,
    ));
    $coupon1->setIsUsed(true);
    $coupon1->setIsEnabled(true);
    $date = new \DateTime();
    $coupon1->setExpirationDate($date->setTimestamp(strtotime("today + 3 months")));

    $condition1 = new MatchForTotalAmount($adapter);
    $operators = array(
        MatchForTotalAmount::CART_TOTAL => Operators::SUPERIOR,
        MatchForTotalAmount::CART_CURRENCY => Operators::EQUAL
    );
    $values = array(
        MatchForTotalAmount::CART_TOTAL => 40.00,
        MatchForTotalAmount::CART_CURRENCY => 'EUR'
    );
    $condition1->setValidatorsFromForm($operators, $values);

    $condition2 = new MatchForTotalAmount($adapter);
    $operators = array(
        MatchForTotalAmount::CART_TOTAL => Operators::INFERIOR,
        MatchForTotalAmount::CART_CURRENCY => Operators::EQUAL
    );
    $values = array(
        MatchForTotalAmount::CART_TOTAL => 400.00,
        MatchForTotalAmount::CART_CURRENCY => 'EUR'
    );
    $condition2->setValidatorsFromForm($operators, $values);

    $conditions = new ConditionCollection();
    $conditions[] = $condition1;
    $conditions[] = $condition2;
    /** @var ConditionFactory $conditionFactory */
    $conditionFactory = $container->get('thelia.condition.factory');

    $serializedConditions = $conditionFactory->serializeConditionCollection($conditions);
    $coupon1->setSerializedConditions($serializedConditions);
    $coupon1->setMaxUsage(40);
    $coupon1->setIsCumulative(true);
    $coupon1->setIsRemovingPostage(false);
    $coupon1->setIsAvailableOnSpecialOffers(true);
    $coupon1->save();

    // Coupons
    $coupon2 = new Thelia\Model\Coupon();
    $coupon2->setCode('SPRINGBREAK');
    $coupon2->setType('thelia.coupon.type.remove_x_percent');
    $coupon2->setTitle('Springbreak coupon');
    $coupon2->setShortDescription('Coupon for Springbreak removing 10% if you have more than 4 articles in your cart');
    $coupon2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at luctus tellus. Integer turpis mauris, aliquet vitae risus tristique, pellentesque vestibulum urna. Vestibulum sodales laoreet lectus dictum suscipit. Praesent vulputate, sem id varius condimentum, quam magna tempor elit, quis venenatis ligula nulla eget libero. Cras egestas euismod tellus, id pharetra leo suscipit quis. Donec lacinia ac lacus et ultricies. Nunc in porttitor neque. Proin at quam congue, consectetur orci sed, congue nulla. Nulla eleifend nunc ligula, nec pharetra elit tempus quis. Vivamus vel mauris sed est dictum blandit. Maecenas blandit dapibus velit ut sollicitudin. In in euismod mauris, consequat viverra magna. Cras velit velit, sollicitudin commodo tortor gravida, tempus varius nulla.

Donec rhoncus leo mauris, id porttitor ante luctus tempus. Curabitur quis augue feugiat, ullamcorper mauris ac, interdum mi. Quisque aliquam lorem vitae felis lobortis, id interdum turpis mattis. Vestibulum diam massa, ornare congue blandit quis, facilisis at nisl. In tortor metus, venenatis non arcu nec, sollicitudin ornare nisl. Nunc erat risus, varius nec urna at, iaculis lacinia elit. Aenean ut felis tempus, tincidunt odio non, sagittis nisl. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec vitae hendrerit elit. Nunc sit amet gravida risus, euismod lobortis massa. Nam a erat mauris. Nam a malesuada lorem. Nulla id accumsan dolor, sed rhoncus tellus. Quisque dictum felis sed leo auctor, at volutpat lectus viverra. Morbi rutrum, est ac aliquam imperdiet, nibh sem sagittis justo, ac mattis magna lacus eu nulla.

Duis interdum lectus nulla, nec pellentesque sapien condimentum at. Suspendisse potenti. Sed eu purus tellus. Nunc quis rhoncus metus. Fusce vitae tellus enim. Interdum et malesuada fames ac ante ipsum primis in faucibus. Etiam tempor porttitor erat vitae iaculis. Sed est elit, consequat non ornare vitae, vehicula eget lectus. Etiam consequat sapien mauris, eget consectetur magna imperdiet eget. Nunc sollicitudin luctus velit, in commodo nulla adipiscing fermentum. Fusce nisi sapien, posuere vitae metus sit amet, facilisis sollicitudin dui. Fusce ultricies auctor enim sit amet iaculis. Morbi at vestibulum enim, eget adipiscing eros.

Praesent ligula lorem, faucibus ut metus quis, fermentum iaculis erat. Pellentesque elit erat, lacinia sed semper ac, sagittis vel elit. Nam eu convallis est. Curabitur rhoncus odio vitae consectetur pellentesque. Nam vitae arcu nec ante scelerisque dignissim vel nec neque. Suspendisse augue nulla, mollis eget dui et, tempor facilisis erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac diam ipsum. Donec convallis dui ultricies velit auctor, non lobortis nulla ultrices. Morbi vitae dignissim ante, sit amet lobortis tortor. Nunc dapibus condimentum augue, in molestie neque congue non.

Sed facilisis pellentesque nisl, eu tincidunt erat scelerisque a. Nullam malesuada tortor vel erat volutpat tincidunt. In vehicula diam est, a convallis eros scelerisque ut. Donec aliquet venenatis iaculis. Ut a arcu gravida, placerat dui eu, iaculis nisl. Quisque adipiscing orci sit amet dui dignissim lacinia. Sed vulputate lorem non dolor adipiscing ornare. Morbi ornare id nisl id aliquam. Ut fringilla elit ante, nec lacinia enim fermentum sit amet. Aenean rutrum lorem eu convallis pharetra. Cras malesuada varius metus, vitae gravida velit. Nam a varius ipsum, ac commodo dolor. Phasellus nec elementum elit. Etiam vel adipiscing leo.');
    $coupon2->setEffects(array(
        RemoveXPercent::INPUT_PERCENTAGE_NAME => 10.00
    ));
    $coupon2->setIsUsed(true);
    $coupon2->setIsEnabled(true);
    $date = new \DateTime();
    $coupon2->setExpirationDate($date->setTimestamp(strtotime("today + 1 months")));

    $condition1 = new MatchForXArticles($adapter);
    $operators = array(
        MatchForXArticles::CART_QUANTITY => Operators::SUPERIOR,
    );
    $values = array(
        MatchForXArticles::CART_QUANTITY => 4,
    );
    $condition1->setValidatorsFromForm($operators, $values);
    $conditions = new ConditionCollection();
    $conditions[] = $condition1;

    /** @var ConditionFactory $conditionFactory */
    $conditionFactory = $container->get('thelia.condition.factory');

    $serializedConditions = $conditionFactory->serializeConditionCollection($conditions);
    $coupon2->setSerializedConditions($serializedConditions);
    $coupon2->setMaxUsage(-1);
    $coupon2->setIsCumulative(false);
    $coupon2->setIsRemovingPostage(true);
    $coupon2->setIsAvailableOnSpecialOffers(true);
    $coupon2->save();

    // Coupons
    $coupon3 = new Thelia\Model\Coupon();
    $coupon3->setCode('OLD');
    $coupon3->setType('thelia.coupon.type.remove_x_percent');
    $coupon3->setTitle('Old coupon');
    $coupon3->setShortDescription('Coupon for Springbreak removing 10% if you have more than 4 articles in your cart');
    $coupon3->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras at luctus tellus. Integer turpis mauris, aliquet vitae risus tristique, pellentesque vestibulum urna. Vestibulum sodales laoreet lectus dictum suscipit. Praesent vulputate, sem id varius condimentum, quam magna tempor elit, quis venenatis ligula nulla eget libero. Cras egestas euismod tellus, id pharetra leo suscipit quis. Donec lacinia ac lacus et ultricies. Nunc in porttitor neque. Proin at quam congue, consectetur orci sed, congue nulla. Nulla eleifend nunc ligula, nec pharetra elit tempus quis. Vivamus vel mauris sed est dictum blandit. Maecenas blandit dapibus velit ut sollicitudin. In in euismod mauris, consequat viverra magna. Cras velit velit, sollicitudin commodo tortor gravida, tempus varius nulla.

Donec rhoncus leo mauris, id porttitor ante luctus tempus. Curabitur quis augue feugiat, ullamcorper mauris ac, interdum mi. Quisque aliquam lorem vitae felis lobortis, id interdum turpis mattis. Vestibulum diam massa, ornare congue blandit quis, facilisis at nisl. In tortor metus, venenatis non arcu nec, sollicitudin ornare nisl. Nunc erat risus, varius nec urna at, iaculis lacinia elit. Aenean ut felis tempus, tincidunt odio non, sagittis nisl. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec vitae hendrerit elit. Nunc sit amet gravida risus, euismod lobortis massa. Nam a erat mauris. Nam a malesuada lorem. Nulla id accumsan dolor, sed rhoncus tellus. Quisque dictum felis sed leo auctor, at volutpat lectus viverra. Morbi rutrum, est ac aliquam imperdiet, nibh sem sagittis justo, ac mattis magna lacus eu nulla.

Duis interdum lectus nulla, nec pellentesque sapien condimentum at. Suspendisse potenti. Sed eu purus tellus. Nunc quis rhoncus metus. Fusce vitae tellus enim. Interdum et malesuada fames ac ante ipsum primis in faucibus. Etiam tempor porttitor erat vitae iaculis. Sed est elit, consequat non ornare vitae, vehicula eget lectus. Etiam consequat sapien mauris, eget consectetur magna imperdiet eget. Nunc sollicitudin luctus velit, in commodo nulla adipiscing fermentum. Fusce nisi sapien, posuere vitae metus sit amet, facilisis sollicitudin dui. Fusce ultricies auctor enim sit amet iaculis. Morbi at vestibulum enim, eget adipiscing eros.

Praesent ligula lorem, faucibus ut metus quis, fermentum iaculis erat. Pellentesque elit erat, lacinia sed semper ac, sagittis vel elit. Nam eu convallis est. Curabitur rhoncus odio vitae consectetur pellentesque. Nam vitae arcu nec ante scelerisque dignissim vel nec neque. Suspendisse augue nulla, mollis eget dui et, tempor facilisis erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac diam ipsum. Donec convallis dui ultricies velit auctor, non lobortis nulla ultrices. Morbi vitae dignissim ante, sit amet lobortis tortor. Nunc dapibus condimentum augue, in molestie neque congue non.

Sed facilisis pellentesque nisl, eu tincidunt erat scelerisque a. Nullam malesuada tortor vel erat volutpat tincidunt. In vehicula diam est, a convallis eros scelerisque ut. Donec aliquet venenatis iaculis. Ut a arcu gravida, placerat dui eu, iaculis nisl. Quisque adipiscing orci sit amet dui dignissim lacinia. Sed vulputate lorem non dolor adipiscing ornare. Morbi ornare id nisl id aliquam. Ut fringilla elit ante, nec lacinia enim fermentum sit amet. Aenean rutrum lorem eu convallis pharetra. Cras malesuada varius metus, vitae gravida velit. Nam a varius ipsum, ac commodo dolor. Phasellus nec elementum elit. Etiam vel adipiscing leo.');
    $coupon3->setEffects(array(
        RemoveXPercent::INPUT_PERCENTAGE_NAME => 10.00,
    ));
    $coupon3->setIsUsed(false);
    $coupon3->setIsEnabled(false);
    $date = new \DateTime();
    $coupon3->setExpirationDate($date->setTimestamp(strtotime("today + 2 months")));

    $condition1 = new MatchForEveryone($adapter);
    $operators = array();
    $values = array();
    $condition1->setValidatorsFromForm($operators, $values);
    $conditions = new ConditionCollection();
    $conditions[] = $condition1;

    /** @var ConditionFactory $constraintCondition */
    $constraintCondition = $container->get('thelia.condition.factory');

    $serializedConditions = $constraintCondition->serializeConditionCollection($conditions);
    $coupon3->setSerializedConditions($serializedConditions);
    $coupon3->setMaxUsage(-1);
    $coupon3->setIsCumulative(true);
    $coupon3->setIsRemovingPostage(false);
    $coupon3->setIsAvailableOnSpecialOffers(false);
    $coupon3->save();
}
