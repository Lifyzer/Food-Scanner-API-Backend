<?php

namespace Lifyzer\Api;

define('ENCRYPTION_KEY', 'niplframework');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DEFAULT_NO_RECORDS', 'No records found.');
define('SUCCESS', 'success');
define('FAILED', 'failed');
define('APPNAME', 'Lifyzer App');
define('SENDER_EMAIL_ID', 'pra@narola.email');
define('SENDER_EMAIL_PASSWORD', 'jUVAO8ufUmaucHM');

define('ENCRYPTION_KEY_IV', '@#$%!@#$#$%!@#$%');

define('YES', 'yes');
define('NO', 'no');
define('ERROR', 'error');
define('STATUS_KEY', 'status');
define('MESSAGE_KEY', 'message');
define('IS_DELETE', '0');
define('NO_ERROR', 'No error');
define('UPDATE_SUCCESS', 'update Success');
define('USERTOKEN', 'UserToken');

// ************  Messages  ****************//
define('SOMETHING_WENT_WRONG_TRY_AGAIN_LATER', 'Something went wrong, Please try again later');
define('EMAIL_ALREADY_EXISTS', 'Email ID already exists');
define('REGISTRATION_SUCCESSFULLY_DONE', 'Registration successfully done');
define('MALICIOUS_SOURCE', 'Malicious source detected');
define('TOKEN_ERROR', 'Please ensure that security token is supplied in your request');
define('DEFAULT_NO_RECORD', 'No record found');
define('WRONG_PASSWORD_MESSAGE', 'You have entered a wrong password');
define('CHNG_WRONG_PASSWORD_MESSAGE', 'Old password is incorrect');
define('NO_DATA_AVAILABLE', 'No data available');
define('NO_EMAIL_AND_PASSWORD_AVAILABLE', 'Email ID or Password is incorrect.');
define('USER_LOGIN_SUCCESSFULLY', 'User Login Successfully');
define('PASSWORD_CHANGED', 'Password successfully changed!');
define('PASSWORD_SENT', 'Password is sent successfully');
define('NO_FAVOURITE_PRODUCT_FOUND', 'No Favourite Product not found');
define('NO_FAVOURITE_HISTORY_FOUND', 'No History not found');
define('NO_PRODUCT_FOUND_IN_DATABASE', 'No Product found in database');
define('DATA_FETCHED_SUCCESSFULLY', 'Data fetched successfully');
define('HISTORY_REMOVED_SUCCESSFULLY', 'History deleted successfully');
define('FAVOURITE_SUCCESSFULLY', ' Add / Removed to favourite Successfully');
define('PROFILE_UPDATED_SUCCESSFULLY', 'Profile Updated Successfully');

abstract class DELETE_STATUS
{
    const IS_DELETE = '1';
    const NOT_DELETE = '0';
}
