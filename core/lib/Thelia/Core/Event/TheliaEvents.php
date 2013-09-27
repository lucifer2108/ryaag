<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Thelia\Core\Event;

/**
 *
 * This class contains all Thelia events identifiers used by Thelia Core
 *
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */

final class TheliaEvents
{

    /**
     * sent at the beginning
     */
    const BOOT = "thelia.boot";

    /**
     * ACTION event
     *
     * Sent if no action are already present in Thelia action process ( see Thelia\Routing\Matcher\ActionMatcher)
     */
    const ACTION = "thelia.action";

    /**
     * INCLUDE event
     *
     * Sent before starting thelia inclusion
     */
    const INCLUSION = "thelia.include";

    /**
     * Sent before the logout of the customer.
     */
    const CUSTOMER_LOGOUT = "action.customer_logout";
    /**
     * Sent once the customer is successfully logged in.
     */
    const CUSTOMER_LOGIN  = "action.customer_login";

    /**
     * sent on customer account creation
     */
    const CUSTOMER_CREATEACCOUNT = "action.createCustomer";

    /**
     * sent on customer account update
     */
    const CUSTOMER_UPDATEACCOUNT = "action.updateCustomer";

    /**
     * sent on customer removal
     */
    const CUSTOMER_DELETEACCOUNT = "action.deleteCustomer";

    /**
     * sent when a customer need a new password
     */
    const LOST_PASSWORD = "action.lostPassword";
    /**
     * Sent before the logout of the administrator.
     */
    const ADMIN_LOGOUT = "action.admin_logout";
    /**
     * Sent once the administrator is successfully logged in.
     */
    const ADMIN_LOGIN  = "action.admin_login";

    /**
     * Sent once the customer creation form has been successfully validated, and before customer insertion in the database.
     */
    const BEFORE_CREATECUSTOMER = "action.before_createcustomer";

    /**
     * Sent just after a successful insert of a new customer in the database.
     */
    const AFTER_CREATECUSTOMER 	= "action.after_createcustomer";

    /**
     * Sent once the customer change form has been successfully validated, and before customer update in the database.
     */
    const BEFORE_UPDATECUSTOMER = "action.before_updateCustomer";
    /**
     * Sent just after a successful update of a customer in the database.
     */
    const AFTER_UPDATECUSTOMER 	= "action.after_updateCustomer";

    /**
     * sent just before customer removal
     */
    const BEFORE_DELETECUSTOMER = "action.before_updateCustomer";

    /**
     * sent just after customer removal
     */
    const AFTER_DELETECUSTOMER = "action.after_deleteCustomer";

    // -- ADDRESS MANAGEMENT ---------------------------------------------------------
    /**
     * sent for address creation
     */
    const ADDRESS_CREATE = "action.createAddress";

    /**
     * sent for address creation
     */
    const ADDRESS_UPDATE = "action.updateAddress";

    /**
     * sent on address removal
     */
    const ADDRESS_DELETE = "action.deleteAddress";

    const BEFORE_CREATEADDRESS = "action.before_createAddress";
    const AFTER_CREATEADDRESS  = "action.after_createAddress";

    const BEFORE_UPDATEADDRESS = "action.before_updateAddress";
    const AFTER_UPDATEADDRESS = "action.after_updateAddress";

    const BEFORE_DELETEADDRESS = "action.before_deleteAddress";
    const AFTER_DELETEADDRESS = "action.after_deleteAddress";

    // -- END ADDRESS MANAGEMENT ---------------------------------------------------------

    // -- Categories management -----------------------------------------------

    const CATEGORY_CREATE            = "action.createCategory";
    const CATEGORY_UPDATE            = "action.updateCategory";
    const CATEGORY_DELETE            = "action.deleteCategory";
    const CATEGORY_TOGGLE_VISIBILITY = "action.toggleCategoryVisibility";
    const CATEGORY_UPDATE_POSITION   = "action.updateCategoryPosition";

    const CATEGORY_ADD_CONTENT      = "action.categoryAddContent";
    const CATEGORY_REMOVE_CONTENT   = "action.categoryRemoveContent";

    const BEFORE_CREATECATEGORY = "action.before_createcategory";
    const AFTER_CREATECATEGORY 	= "action.after_createcategory";

    const BEFORE_DELETECATEGORY = "action.before_deletecategory";
    const AFTER_DELETECATEGORY 	= "action.after_deletecategory";

    const BEFORE_UPDATECATEGORY = "action.before_updateCategory";
    const AFTER_UPDATECATEGORY 	= "action.after_updateCategory";

    // -- folder management -----------------------------------------------

    const FOLDER_CREATE            = "action.createFolder";
    const FOLDER_UPDATE            = "action.updateFolder";
    const FOLDER_DELETE            = "action.deleteFolder";
    const FOLDER_TOGGLE_VISIBILITY = "action.toggleFolderVisibility";
    const FOLDER_UPDATE_POSITION   = "action.updateFolderPosition";

//    const FOLDER_ADD_CONTENT      = "action.categoryAddContent";
//    const FOLDER_REMOVE_CONTENT   = "action.categoryRemoveContent";

    const BEFORE_CREATEFOLDER = "action.before_createFolder";
    const AFTER_CREATEFOLDER 	= "action.after_createFolder";

    const BEFORE_DELETEFOLDER = "action.before_deleteFolder";
    const AFTER_DELETEFOLDER 	= "action.after_deleteFolder";

    const BEFORE_UPDATEFOLDER = "action.before_updateFolder";
    const AFTER_UPDATEFOLDER 	= "action.after_updateFolder";

    // -- content management -----------------------------------------------

    const CONTENT_CREATE            = "action.createContent";
    const CONTENT_UPDATE            = "action.updateContent";
    const CONTENT_DELETE            = "action.deleteContent";
    const CONTENT_TOGGLE_VISIBILITY = "action.toggleContentVisibility";
    const CONTENT_UPDATE_POSITION   = "action.updateContentPosition";

//    const FOLDER_ADD_CONTENT      = "action.categoryAddContent";
//    const FOLDER_REMOVE_CONTENT   = "action.categoryRemoveContent";

    const BEFORE_CREATECONTENT = "action.before_createContent";
    const AFTER_CREATECONTENT	= "action.after_createContent";

    const BEFORE_DELETECONTENT = "action.before_deleteContent";
    const AFTER_DELETECONTENT 	= "action.after_deleteContent";

    const BEFORE_UPDATECONTENT = "action.before_updateContent";
    const AFTER_UPDATECONTENT 	= "action.after_updateContent";

    // -- Categories Associated Content ----------------------------------------

    const BEFORE_CREATECATEGORY_ASSOCIATED_CONTENT   = "action.before_createCategoryAssociatedContent";
    const AFTER_CREATECATEGORY_ASSOCIATED_CONTENT 	= "action.after_createCategoryAssociatedContent";

    const BEFORE_DELETECATEGORY_ASSOCIATED_CONTENT   = "action.before_deleteCategoryAssociatedContent";
    const AFTER_DELETECATEGORY_ASSOCIATED_CONTENT 	= "action.after_deleteCategoryAssociatedContent";

    const BEFORE_UPDATECATEGORY_ASSOCIATED_CONTENT   = "action.before_updateCategoryAssociatedContent";
    const AFTER_UPDATECATEGORY_ASSOCIATED_CONTENT 	= "action.after_updateCategoryAssociatedContent";

    // -- Product management -----------------------------------------------

    const PRODUCT_CREATE            = "action.createProduct";
    const PRODUCT_UPDATE            = "action.updateProduct";
    const PRODUCT_DELETE            = "action.deleteProduct";
    const PRODUCT_TOGGLE_VISIBILITY = "action.toggleProductVisibility";
    const PRODUCT_UPDATE_POSITION   = "action.updateProductPosition";

    const PRODUCT_ADD_CONTENT             = "action.productAddContent";
    const PRODUCT_REMOVE_CONTENT          = "action.productRemoveContent";
    const PRODUCT_UPDATE_CONTENT_POSITION = "action.updateProductContentPosition";

    const PRODUCT_ADD_COMBINATION    = "action.productAddCombination";
    const PRODUCT_DELETE_COMBINATION = "action.productDeleteCombination";

    const PRODUCT_SET_TEMPLATE = "action.productSetTemplate";

    const PRODUCT_ADD_ACCESSORY             = "action.productAddProductAccessory";
    const PRODUCT_REMOVE_ACCESSORY          = "action.productRemoveProductAccessory";
    const PRODUCT_UPDATE_ACCESSORY_POSITION = "action.updateProductAccessoryPosition";

    const PRODUCT_FEATURE_UPDATE_VALUE = "action.updateProductFeatureValue";
    const PRODUCT_FEATURE_DELETE_VALUE = "action.deleteProductFeatureValue";

    const PRODUCT_ADD_CATEGORY    = "action.addProductCategory";
    const PRODUCT_REMOVE_CATEGORY = "action.deleteProductCategory";

    const BEFORE_CREATEPRODUCT = "action.before_createproduct";
    const AFTER_CREATEPRODUCT  = "action.after_createproduct";

    const BEFORE_DELETEPRODUCT = "action.before_deleteproduct";
    const AFTER_DELETEPRODUCT  = "action.after_deleteproduct";

    const BEFORE_UPDATEPRODUCT = "action.before_updateProduct";
    const AFTER_UPDATEPRODUCT  = "action.after_updateProduct";

    // -- Product Accessories --------------------------------------------------

    const BEFORE_CREATEACCESSORY = "action.before_createAccessory";
    const AFTER_CREATEACCESSORY  = "action.after_createAccessory";

    const BEFORE_DELETEACCESSORY = "action.before_deleteAccessory";
    const AFTER_DELETEACCESSORY  = "action.after_deleteAccessory";

    const BEFORE_UPDATEACCESSORY = "action.before_updateAccessory";
    const AFTER_UPDATEACCESSORY  = "action.after_updateAccessory";

    // -- Product Associated Content -------------------------------------------

    const BEFORE_CREATEPRODUCT_ASSOCIATED_CONTENT   = "action.before_createProductAssociatedContent";
    const AFTER_CREATEPRODUCT_ASSOCIATED_CONTENT 	= "action.after_createProductAssociatedContent";

    const BEFORE_DELETEPRODUCT_ASSOCIATED_CONTENT   = "action.before_deleteProductAssociatedContent";
    const AFTER_DELETEPRODUCT_ASSOCIATED_CONTENT 	= "action.after_deleteProductAssociatedContent";

    const BEFORE_UPDATEPRODUCT_ASSOCIATED_CONTENT   = "action.before_updateProductAssociatedContent";
    const AFTER_UPDATEPRODUCT_ASSOCIATED_CONTENT 	= "action.after_updateProductAssociatedContent";

    // -- Feature product ------------------------------------------------------

    const BEFORE_CREATEFEATURE_PRODUCT = "action.before_createFeatureProduct";
    const AFTER_CREATEFEATURE_PRODUCT  = "action.after_createFeatureProduct";

    const BEFORE_DELETEFEATURE_PRODUCT = "action.before_deleteFeatureProduct";
    const AFTER_DELETEFEATURE_PRODUCT  = "action.after_deleteFeatureProduct";

    const BEFORE_UPDATEFEATURE_PRODUCT = "action.before_updateFeatureProduct";
    const AFTER_UPDATEFEATURE_PRODUCT  = "action.after_updateFeatureProduct";

    /**
     * sent when a new existing cat id duplicated. This append when current customer is different from current cart
     */
    const CART_DUPLICATE = "cart.duplicate";

    /**
     * sent when a new item is added to current cart
     */
    const AFTER_CARTADDITEM = "cart.after.addItem";

    /**
     * sent when a cart item is modify
     */
    const AFTER_CARTUPDATEITEM = "cart.updateItem";

    /**
     * sent for addArticle action
     */
    const CART_ADDITEM = "action.addArticle";

    /**
     * sent on modify article action
     */
    const CART_UPDATEITEM = "action.updateArticle";

    const CART_DELETEITEM = "action.deleteArticle";

    /**
     * Order linked event
     */
    const ORDER_SET_DELIVERY_ADDRESS = "action.order.setDeliveryAddress";
    const ORDER_SET_DELIVERY_MODULE = "action.order.setDeliveryModule";
    const ORDER_SET_INVOICE_ADDRESS = "action.order.setInvoiceAddress";
    const ORDER_SET_PAYMENT_MODULE = "action.order.setPaymentModule";
    const ORDER_PAY = "action.order.pay";
    const ORDER_BEFORE_CREATE = "action.order.beforeCreate";
    const ORDER_AFTER_CREATE = "action.order.afterCreate";
    const ORDER_BEFORE_PAYMENT = "action.order.beforePayment";

    const ORDER_PRODUCT_BEFORE_CREATE = "action.orderProduct.beforeCreate";
    const ORDER_PRODUCT_AFTER_CREATE = "action.orderProduct.afterCreate";

    /**
     * Sent on image processing
     */
    const IMAGE_PROCESS = "action.processImage";

    /**
     * Sent on document processing
     */
    const DOCUMENT_PROCESS = "action.processDocument";

    /**
     * Sent on image cache clear request
     */
    const DOCUMENT_CLEAR_CACHE = "action.clearDocumentCache";

    /**
     * Save given documents
     */
    const DOCUMENT_SAVE = "action.saveDocument";

    /**
     * Save given documents
     */
    const DOCUMENT_UPDATE = "action.updateDocument";

    /**
     * Delete given document
     */
    const DOCUMENT_DELETE = "action.deleteDocument";

    /**
     * Sent on image cache clear request
     */
    const IMAGE_CLEAR_CACHE = "action.clearImageCache";

    /**
     * Save given images
     */
    const IMAGE_SAVE = "action.saveImages";

    /**
     * Save given images
     */
    const IMAGE_UPDATE = "action.updateImages";

    /**
     * Delete given image
     */
    const IMAGE_DELETE = "action.deleteImage";

    /**
     * Sent when creating a Coupon
     */
    const COUPON_CREATE = "action.create_coupon";

    /**
     * Sent just before a successful insert of a new Coupon in the database.
     */
    const BEFORE_CREATE_COUPON 	= "action.before_create_coupon";

    /**
     * Sent just after a successful insert of a new Coupon in the database.
     */
    const AFTER_CREATE_COUPON 	= "action.after_create_coupon";

    /**
     * Sent when editing a Coupon
     */
    const COUPON_UPDATE = "action.update_coupon";

    /**
     * Sent just before a successful update of a new Coupon in the database.
     */
    const BEFORE_UPDATE_COUPON 	= "action.before_update_coupon";

    /**
     * Sent just after a successful update of a new Coupon in the database.
     */
    const AFTER_UPDATE_COUPON 	= "action.after_update_coupon";

    /**
     * Sent when attempting to use a Coupon
     */
    const COUPON_CONSUME 	= "action.consume_coupon";

    /**
     * Sent just before an attempt to use a Coupon
     */
    const BEFORE_CONSUME_COUPON 	= "action.before_consume_coupon";

    /**
     * Sent just after an attempt to use a Coupon
     */
    const AFTER_CONSUME_COUPON 	= "action.after_consume_coupon";

    /**
     * Sent when attempting to update Coupon Condition
     */
    const COUPON_CONDITION_UPDATE 	= "action.update_coupon_condition";

    /**
     * Sent just before an attempt to update a Coupon Condition
     */
    const BEFORE_COUPON_CONDITION_UPDATE 	= "action.before_update_coupon_condition";

    /**
     * Sent just after an attempt to update a Coupon Condition
     */
    const AFTER_COUPON_CONDITION_UPDATE 	= "action.after_update_coupon_condition";

    // -- Configuration management ---------------------------------------------

    const CONFIG_CREATE   = "action.createConfig";
    const CONFIG_SETVALUE = "action.setConfigValue";
    const CONFIG_UPDATE   = "action.updateConfig";
    const CONFIG_DELETE   = "action.deleteConfig";

    const BEFORE_CREATECONFIG = "action.before_createConfig";
    const AFTER_CREATECONFIG  = "action.after_createConfig";

    const BEFORE_UPDATECONFIG = "action.before_updateConfig";
    const AFTER_UPDATECONFIG  = "action.after_updateConfig";

    const BEFORE_DELETECONFIG = "action.before_deleteConfig";
    const AFTER_DELETECONFIG  = "action.after_deleteConfig";

    // -- Messages management ---------------------------------------------

    const MESSAGE_CREATE   = "action.createMessage";
    const MESSAGE_UPDATE   = "action.updateMessage";
    const MESSAGE_DELETE   = "action.deleteMessage";

    const BEFORE_CREATEMESSAGE = "action.before_createMessage";
    const AFTER_CREATEMESSAGE  = "action.after_createMessage";

    const BEFORE_UPDATEMESSAGE = "action.before_updateMessage";
    const AFTER_UPDATEMESSAGE  = "action.after_updateMessage";

    const BEFORE_DELETEMESSAGE = "action.before_deleteMessage";
    const AFTER_DELETEMESSAGE  = "action.after_deleteMessage";

    // -- Currencies management ---------------------------------------------

    const CURRENCY_CREATE          = "action.createCurrency";
    const CURRENCY_UPDATE          = "action.updateCurrency";
    const CURRENCY_DELETE          = "action.deleteCurrency";
    const CURRENCY_SET_DEFAULT     = "action.setDefaultCurrency";
    const CURRENCY_UPDATE_RATES    = "action.updateCurrencyRates";
    const CURRENCY_UPDATE_POSITION = "action.updateCurrencyPosition";

    const BEFORE_CREATECURRENCY = "action.before_createCurrency";
    const AFTER_CREATECURRENCY  = "action.after_createCurrency";

    const BEFORE_UPDATECURRENCY = "action.before_updateCurrency";
    const AFTER_UPDATECURRENCY  = "action.after_updateCurrency";

    const BEFORE_DELETECURRENCY = "action.before_deleteCurrency";
    const AFTER_DELETECURRENCY  = "action.after_deleteCurrency";

    const CHANGE_DEFAULT_CURRENCY = 'action.changeDefaultCurrency';

    // -- Product templates management -----------------------------------------

    const TEMPLATE_CREATE          = "action.createTemplate";
    const TEMPLATE_UPDATE          = "action.updateTemplate";
    const TEMPLATE_DELETE          = "action.deleteTemplate";

    const TEMPLATE_ADD_ATTRIBUTE    = "action.templateAddAttribute";
    const TEMPLATE_DELETE_ATTRIBUTE = "action.templateDeleteAttribute";

    const TEMPLATE_ADD_FEATURE    = "action.templateAddFeature";
    const TEMPLATE_DELETE_FEATURE = "action.templateDeleteFeature";

    const TEMPLATE_CHANGE_FEATURE_POSITION   = "action.templateChangeAttributePosition";
    const TEMPLATE_CHANGE_ATTRIBUTE_POSITION = "action.templateChangeFeaturePosition";

    const BEFORE_CREATETEMPLATE = "action.before_createTemplate";
    const AFTER_CREATETEMPLATE  = "action.after_createTemplate";

    const BEFORE_UPDATETEMPLATE = "action.before_updateTemplate";
    const AFTER_UPDATETEMPLATE  = "action.after_updateTemplate";

    const BEFORE_DELETETEMPLATE = "action.before_deleteTemplate";
    const AFTER_DELETETEMPLATE  = "action.after_deleteTemplate";

    // -- Attributes management ---------------------------------------------

    const ATTRIBUTE_CREATE          = "action.createAttribute";
    const ATTRIBUTE_UPDATE          = "action.updateAttribute";
    const ATTRIBUTE_DELETE          = "action.deleteAttribute";
    const ATTRIBUTE_UPDATE_POSITION = "action.updateAttributePosition";

    const ATTRIBUTE_REMOVE_FROM_ALL_TEMPLATES = "action.addAttributeToAllTemplate";
    const ATTRIBUTE_ADD_TO_ALL_TEMPLATES      = "action.removeAttributeFromAllTemplate";

    const BEFORE_CREATEATTRIBUTE = "action.before_createAttribute";
    const AFTER_CREATEATTRIBUTE  = "action.after_createAttribute";

    const BEFORE_UPDATEATTRIBUTE = "action.before_updateAttribute";
    const AFTER_UPDATEATTRIBUTE  = "action.after_updateAttribute";

    const BEFORE_DELETEATTRIBUTE = "action.before_deleteAttribute";
    const AFTER_DELETEATTRIBUTE  = "action.after_deleteAttribute";

    // -- Features management ---------------------------------------------

    const FEATURE_CREATE          = "action.createFeature";
    const FEATURE_UPDATE          = "action.updateFeature";
    const FEATURE_DELETE          = "action.deleteFeature";
    const FEATURE_UPDATE_POSITION = "action.updateFeaturePosition";

    const FEATURE_REMOVE_FROM_ALL_TEMPLATES = "action.addFeatureToAllTemplate";
    const FEATURE_ADD_TO_ALL_TEMPLATES      = "action.removeFeatureFromAllTemplate";

    const BEFORE_CREATEFEATURE = "action.before_createFeature";
    const AFTER_CREATEFEATURE  = "action.after_createFeature";

    const BEFORE_UPDATEFEATURE = "action.before_updateFeature";
    const AFTER_UPDATEFEATURE  = "action.after_updateFeature";

    const BEFORE_DELETEFEATURE = "action.before_deleteFeature";
    const AFTER_DELETEFEATURE  = "action.after_deleteFeature";

    // -- Attributes values management ----------------------------------------

    const ATTRIBUTE_AV_CREATE          = "action.createAttributeAv";
    const ATTRIBUTE_AV_UPDATE          = "action.updateAttributeAv";
    const ATTRIBUTE_AV_DELETE          = "action.deleteAttributeAv";
    const ATTRIBUTE_AV_UPDATE_POSITION = "action.updateAttributeAvPosition";

    const BEFORE_CREATEATTRIBUTE_AV = "action.before_createAttributeAv";
    const AFTER_CREATEATTRIBUTE_AV  = "action.after_createAttributeAv";

    const BEFORE_UPDATEATTRIBUTE_AV = "action.before_updateAttributeAv";
    const AFTER_UPDATEATTRIBUTE_AV  = "action.after_updateAttributeAv";

    const BEFORE_DELETEATTRIBUTE_AV = "action.before_deleteAttributeAv";
    const AFTER_DELETEATTRIBUTE_AV  = "action.after_deleteAttributeAv";


    // -- Features values management ----------------------------------------

    const FEATURE_AV_CREATE          = "action.createFeatureAv";
    const FEATURE_AV_UPDATE          = "action.updateFeatureAv";
    const FEATURE_AV_DELETE          = "action.deleteFeatureAv";
    const FEATURE_AV_UPDATE_POSITION = "action.updateFeatureAvPosition";

    const BEFORE_CREATEFEATURE_AV = "action.before_createFeatureAv";
    const AFTER_CREATEFEATURE_AV  = "action.after_createFeatureAv";

    const BEFORE_UPDATEFEATURE_AV = "action.before_updateFeatureAv";
    const AFTER_UPDATEFEATURE_AV  = "action.after_updateFeatureAv";

    const BEFORE_DELETEFEATURE_AV = "action.before_deleteFeatureAv";
    const AFTER_DELETEFEATURE_AV  = "action.after_deleteFeatureAv";

    /**
     * sent when system find a mailer transporter.
     */
    const MAILTRANSPORTER_CONFIG = 'action.mailertransporter.config';

    /**
     * sent when Thelia try to generate a rewriten url
     */
    const GENERATE_REWRITTENURL = 'action.generate_rewritenurl';

}
