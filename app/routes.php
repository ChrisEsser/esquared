<?php

return [

    ['GET', '/admin/switchUser/[i:userId]', 'AdminController#switchUser'],

    ['GET', '/', 'IndexController#index'],
    ['GET', '', 'IndexController#index'],

    ['GET', '/test', 'PropertyController#test'],

    ['GET', '/login', 'LoginController#login'],
    ['POST', '/process-login', 'LoginController#process'],
    ['GET', '/process-login', 'LoginController#loginRedirectFix'],
    ['GET', '/logout', 'LoginController#logout'],
    ['GET', '/login/reset-password', 'LoginController#resetPassword'],
    ['POST', '/login/process-reset-password', 'LoginController#resetPasswordProcess'],
    ['GET', '/login/password-recovery', 'LoginController#passwordRecovery'],
    ['POST', '/login/process-password-recovery', 'LoginController#passwordRecoveryProcess'],

    ['GET', '/account', 'RenterController#account'],
    ['POST', '/account/save', 'RenterController#saveAccount'],
    ['POST', '/account/password', 'RenterController#password'],
    ['POST', '/account/save-password', 'RenterController#savePassword'],
    ['GET', '/account/remove-ach', 'RenterController#removeAch'],

    ['GET', '/rent-history', 'RenterController#rentHistory'],
    ['GET', '/pay-rent', 'RenterController#payRent'],
    ['POST', '/pay-rent/process/card', 'RenterController#payRentProcessCard'],
    ['POST', '/pay-rent/process/ach', 'RenterController#payRentProcessAch'],
    ['GET', '/confirmation/[*:confirmationNumber]', 'RenterController#payRentConfirmation'],
    ['GET', '/manage-payment', 'RenterController#managePayment'],
    ['POST', '/ach-setup/process', 'RenterController#achSetupProcess'],
    ['POST', '/ach-setup/verify', 'RenterController#achSetupProcess'],

    ['GET', '/dashboard', 'DashboardController#dashboard'],

    ['GET', '/properties', 'PropertyController#properties'],
    ['GET', '/edit-property/[i:propertyId]', 'PropertyController#edit'],
    ['GET', '/add-property', 'PropertyController#edit'],
	['POST', '/save-property', 'PropertyController#save'],
	['POST', '/delete-property/[i:propertyId]', 'PropertyController#delete'],
	['GET', '/property/[i:propertyId]', 'PropertyController#property'],
	['GET', '/property/[i:propertyId]/add-document', 'PropertyController#addDocument'],
	['POST', '/property/[i:propertyId]/save-document', 'PropertyController#saveDocument'],
	['GET', '/property/[i:propertyId]/delete-document', 'PropertyController#deleteDocument'],
//	['GET', '/property/[i:propertyId]/add-payment', 'PropertyController#editPayment'],
//	['GET', '/property/edit-payment/[:paymentId]', 'PropertyController#editPayment'],
//	['POST', '/property/delete-payment/[:paymentId]', 'PropertyController#editPayment'],
//	['POST', '/property/save-payment', 'PropertyController#savePayment'],
	['GET', '/property/[i:propertyId]/delete-image', 'PropertyController#deleteImage'],

    ['GET', '/units', 'UnitController#units'],
    ['GET', '/units/[i:propertyId]', 'UnitController#units'],
    ['GET', '/unit/[i:unitId]', 'UnitController#unit'],
    ['GET', '/edit-unit/[i:unitId]', 'UnitController#edit'],
    ['GET', '/edit-unit/[i:propertyId]', 'UnitController#edit'],
    ['GET', '/add-unit', 'UnitController#edit'],
    ['POST', '/delete-unit/[i:unitId]', 'UnitController#delete'],
	['POST', '/save-unit', 'UnitController#save'],

    ['GET', '/payments', 'PaymentController#payments'],
    ['GET', '/payments/[i:propertyId]/[i:unitId]', 'PaymentController#payments'],
    ['GET', '/payments/[i:propertyId]', 'PaymentController#payments'],
    ['GET', '/payment/[i:paymentIds]', 'PaymentController#payment'],
    ['GET', '/edit-payment/[i:paymentId]', 'PaymentController#edit'],
    ['GET', '/add-payment/[i:propertyId]', 'PaymentController#edit'],
    ['GET', '/add-payment', 'PaymentController#edit'],
    ['POST', '/save-payment', 'PaymentController#save'],
    ['POST', '/delete-payment/[i:paymentId]', 'PaymentController#delete'],

	['GET', '/notes', 'NoteController#notes'],
	['GET', '/note/[i:noteId]', 'NoteController#note'],
    ['GET', '/edit-note/[i:noteId]', 'NoteController#edit'],
    ['GET', '/create-note/[i:propertyId]', 'NoteController#edit'],
    ['GET', '/create-note', 'NoteController#edit'],
    ['POST', '/delete-note/[i:noteId]', 'NoteController#delete'],
	['POST', '/save-note', 'NoteController#save'],

	['GET', '/users', 'UserController#users'],
	['GET', '/edit-user/[i:userId]', 'UserController#edit'],
	['GET', '/create-user', 'UserController#edit'],
	['POST', '/save-user', 'UserController#save'],
	['POST', '/delete-user/[i:userId]', 'UserController#delete'],

	['POST', '/file/upload', 'FileController#upload'],
	['GET', '/file/proxy', 'FileController#proxy'],

	['GET', '/documents', 'DocumentController#documents'],
	['GET', '/edit-document/[i:documentId]', 'DocumentController#edit'],
	['GET', '/create-document', 'DocumentController#edit'],
	['POST', '/save-document', 'DocumentController#save'],
	['POST', '/delete-document/[i:documentId]', 'DocumentController#delete'],

	['GET', '/admin', 'AdminController#admin'],
    ['GET', '/admin/qb/setup', 'AdminController#qbSetup'],
    ['GET', '/admin/qb/callback', 'AdminController#qbCallback'],
    ['POST', '/admin/qb/refresh', 'AdminController#qbRefreshToken'],
    ['GET', '/admin/qb/disconnect', 'AdminController#qbDisconnect'],

    ['GET', '/api/qb/company-info', 'QuickBooksAPIController#companyInfo'],
    ['GET', '/api/qb/accounts', 'QuickBooksAPIController#accounts'],

    ['GET', '/scraper', 'ScraperController#scraper'],
    ['GET', '/edit-scraper/[i:urlId]', 'ScraperController#editScraper'],
    ['GET', '/create-scraper', 'ScraperController#editScraper'],
    ['POST', '/save-scraper', 'ScraperController#saveScraper'],
    ['POST', '/delete-scraper/[i:urlId]', 'ScraperController#deleteScraper'],
    ['GET', '/scraper/[i:urlId]', 'ScraperController#scrape'],
    ['GET', '/scraper/background', 'ScraperController#scrapeBackground'],
    ['GET', '/scraper/[i:urlId]/leads', 'ScraperController#leads'],
    ['GET', '/scraper/leads', 'ScraperController#leads'],
    ['POST', '/delete-lead/[i:leadId]', 'ScraperController#deleteLead'],
    ['POST', '/toggle-lead-active/[i:leadId]/[i:active]', 'ScraperController#toggleLeadActive'],
    ['POST', '/toggle-lead-flagged/[i:leadId]/[i:flagged]', 'ScraperController#toggleLeadFlagged'],
    ['GET', '/edit-lead/[i:leadId]', 'ScraperController#editLead'],
    ['POST', '/save-lead', 'ScraperController#saveLead'],
    ['POST', '/lead/save-address', 'ScraperController#saveAddress'],
    ['GET', '/street-view/[a:type]/[i:addressId]', 'ScraperController#streetView'],
    ['GET', '/street-view/[a:type]/[i:addressId]', 'ScraperController#streetView'],
    ['GET', '/lead/[i:leadId]', 'ScraperController#lead'],
    ['GET', '/lead/[i:leadId]', 'ScraperController#lead'],
    ['GET', '/lead/edit-address/[i:addressId]', 'ScraperController#editAddress'],
    ['GET', '/lead/[i:leadId]/add-address', 'ScraperController#editAddress'],
    ['GET', '/lead/quarantine-address/[i:addressId]', 'ScraperController#quarantineAddress'],
    ['GET', '/scraper/quarantined-addresses', 'ScraperController#quarantineAddresses'],
    ['POST', '/delete-address/[a:type]/[i:addressId]', 'ScraperController#deleteAddress'],




    ['GET', '/test', 'PropertyController#test'],



    ['POST', '/app-data/properties', 'AjaxDataController#properties'],
    ['GET', '/app-data/properties', 'AjaxDataController#properties'],
    ['POST', '/app-data/units', 'AjaxDataController#units'],
    ['POST', '/app-data/documents', 'AjaxDataController#documents'],
    ['POST', '/app-data/notes', 'AjaxDataController#notes'],
    ['POST', '/app-data/users', 'AjaxDataController#users'],
    ['POST', '/app-data/scraper/urls', 'AjaxDataController#scraperUrls'],
    ['POST', '/app-data/scraper/leads', 'AjaxDataController#scraperLeads'],
    ['POST', '/app-data/payments', 'AjaxDataController#payments'],
    ['POST', '/app-data/scraper/quarantine-addresses', 'AjaxDataController#quarantineAddresses'],

];