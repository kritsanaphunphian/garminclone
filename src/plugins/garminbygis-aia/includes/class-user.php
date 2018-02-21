<?php

class User {
    /**
     * @param  string $token
     *
     * @return bool
     */
    public function validate_token( $token ) {
        $api = new AIA_API;

        $result = $api->get_user_info( $token );

        // TODO: Add condition to check if it really successfully made a request to the AIA API.
        $result = json_decode( $result['body'] );

        return $result->IsSuccess;
    }
}
