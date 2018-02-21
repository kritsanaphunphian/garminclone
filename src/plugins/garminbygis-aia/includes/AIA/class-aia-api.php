<?php

class AIA_API {
    /**
     * @var string
     */
    protected $endpoint = 'http://203.151.93.59:94/api/AiaVitality';

    /**
     * @param  string $token
     *
     * @return mixed
     */
    public function get_user_info( $token ) {
        // TODO: Move this mock response to a proper place.
        return [
            'body' => '{"IsSuccess":true,"Reason":null,"memberId":"5503265323","firstName":"ปรางทิพย์","surname":"กิจสดับ","gender":"FEMALE","email":"prazylii@gmail.com","dOB":"1988-04-23+09:00","addressType":"Postal","addressLine1":"XX","addressLine2":null,"addressLine3":null,"suburb":null,"city":"10110","code":"10110","telNoType":"MOBILE","telNo":"(000)0827011141"}'
        ];

        // return wp_remote_get( $this->endpoint . '/Get/' . $token );
    }
}
