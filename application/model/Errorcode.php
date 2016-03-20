<?php

class Errorcode {

    const EMAIL_NO_RECIPIENT = 10000;
    const NEWUSER_REGISTER_MISUSED = 10001;
    
    const PICTURE_INVALID_PATH = 10002;
    const PICTURE_DISALLOWED_OPERATION = 10003;
    const PICTURE_FILE_IS_NO_PIC = 10004;
    const PICTURE_FILE_TOO_BIG = 10005;
    const PICTURE_NO_JPEG = 10006;
    
    const USER_ACCESS_DENIED = 10007;
    
    const ENVOY_WRONG_PUBKEY_FORMAT = 10008;
    const ENVOY_CANT_OVERWRITE_KNOWN_HOST = 10009;
    const ENVOY_DOMAIN_INVALID = 10010;
    
    const DATABASE_STRUCTURE_ERROR = 10011;
    
    const RSA_INVALID_PUB_KEY = 10012;
    
    const COM_CURL_ERROR = 10013;
    
    const RSA_FAILED_TO_SIGN = 10014;
    
    const HASMETA_NODATA = 10015;
    
    const POSTCONTROLLER_POST_NOT_AVAILABEL = 10016;
    const POSTCONTROLLER_POST_DISALLOWED_ACTION = 10017;
    
    const NOTIFICATION_CREATE_ERROR = 10018;

}
