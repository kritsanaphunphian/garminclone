<?php

class User {
    /**
     * @param  string $token
     *
     * @return bool
     */
    public function validate_token($token) {
        // TODO: Perform real check with AIA.
        return $token === 'valid';
    }
}
