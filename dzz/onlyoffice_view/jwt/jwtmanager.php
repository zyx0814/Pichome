<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2021
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

require_once( dirname(__FILE__) . '/lib/BeforeValidException.php' );
require_once( dirname(__FILE__) . '/lib/ExpiredException.php' );
require_once( dirname(__FILE__) . '/lib/SignatureInvalidException.php' );
require_once( dirname(__FILE__) . '/lib/JWT.php' );

// check if a secret key to generate token exists or not
function isJwtEnabled($secret='') {
    return !empty($secret);
}

// encode a payload object into a token using a secret key
function jwtEncode($payload,$secret) {
    return \Firebase\JWT\JWT::encode($payload, $secret);
}

// decode a token into a payload object using a secret key
function jwtDecode($token,$secret) {
    try {
        $payload = \Firebase\JWT\JWT::decode($token, $secret, array("HS256"));
    } catch (\UnexpectedValueException $e) {
        $payload = "";
    }

    return $payload;
}
?>