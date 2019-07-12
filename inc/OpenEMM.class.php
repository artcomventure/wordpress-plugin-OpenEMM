<?php

// OpenEMM Webservice API 2.0
// @ink http://docplayer.org/14331622-Emm-openemm-webservice-api-2-0.html

// @link https://github.com/ronoaldo/openemm/blob/master/openemm-ws/src/main/scripts/WSSESoapClient.php
include_once( 'WSSESoapClient.class.php' );

class OpenEMM
{

    protected $_soap = null;
    protected $_wsdlUrl = '';
    protected $_username = '';
    protected $_password = '';

    /**
     * @param $wsdlUrl
     * @param $username
     * @param $password
     * @param null $prefix
     * @param array $options
     * @throws Exception
     */
    public function __construct( $wsdlUrl, $username, $password, $prefix = null, $options = array() )
    {
        $curl = curl_init( $wsdlUrl );

        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $curl, CURLOPT_HEADER, TRUE );
        curl_setopt( $curl, CURLOPT_NOBODY, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

        $response = curl_exec( $curl );
        curl_close( $curl );

        if ( $response ) {
            $this->_soap = new WsseSoapClient( $wsdlUrl, $username, $password, $prefix, $options );
            // list of all available webservices
//            var_dump( $this->_soap->__getFunctions() ); die;
        }
        else throw new Exception( sprintf( __( 'Unable to load webservice%s.', 'openemm' ), $wsdlUrl ? ' ' . __('at %s', $wsdlUrl, 'openemm') : '' ) );
    }

    /**
     * Check if subscriber exists.
     * @param string $email
     * @return int
     */
    public function subscriberExists( $email )
    {
        return $this->_soap->FindSubscriber( array(
	        'keyColumn' => 'email',
	        'value' => $email
        ) )->value;
    }

	/**
	 * Get (existing or just added) subscriber ID.
	 * @param $email
	 * @param bool $update
	 * @return int
	 */
    public function getSubscriberID( $email, $update = false )
    {
    	$subsriber = openemm_get_subscriber( $email, 'email' );
    	$parameters = array( 'email' => $email ) + $subsriber->data + array(
			'mailtype' => 1, // html
			'gender' => 2, // unknown
		);

        if ( !$customerID = $this->subscriberExists( $email ) )
        	// create subscriber
        	$customerID = $this->_soap->AddSubscriber( array(
	            'keyColumn' => 'email',
	            'doubleCheck' => true,
	            'overwrite' => false,
	            'parameters' => $this->_mapParameters( $parameters ),
	        ) )->customerID;
        // update (existing) subscriber parameters
        elseif ( $update ) $this->_soap->UpdateSubscriber( array(
	        'customerID' => $customerID,
	        'parameters' => $this->_mapParameters( $parameters ),
        ) );

        return $customerID;
    }

	/**
	 * @param int|string $subscriberID
	 * @param int $mailinglist
	 *
	 * @return mixed
	 */
    public function getBindingStatus( $subscriberID, $mailinglist )
    {
	    // in case of email
	    if ( is_email( $subscriberID ) ) {
		    $subscriberID = $this->getSubscriberID( $subscriberID, true );
	    }

    	if ( $subscriberID ) return $this->_soap->GetSubscriberBinding( array(
    		'customerID' => $subscriberID,
		    'mailinglistID' => $mailinglist,
		    'mediatype' => 0
		) )->status;

	    return 0;
    }

	/**
	 * Set the user's mailing list binding.
	 * @param int|string $subscriberID
	 * @return $this
	 */
	public function setSubscription( $subscriberID )
	{
		// in case of email
		if ( is_email( $subscriberID ) ) {
			$subscriberID = $this->getSubscriberID( $subscriberID, true );
		}

		// eventually subscribe to mailing list
		if ( $subscriberID ) $this->_soap->SetSubscriberBinding( array(
			'customerID' => $subscriberID,
			'mailinglistID' => openemm_get_settings()['mailinglist'],
			'status' => 1, // subscribe
			'userType' => 'W', // 'normal' recipient
			'remark' => 'Subscribed via ' . home_url(),
			'mediatype' => 0, // 0 => Email; 4 => SMS
			'exitMailingID' => 0 // ID of unsubscribe mailing
		) );

		return $this;
	}

    /**
     * @param array $parameters
     * @return array
     */
    private function _mapParameters(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = array(
                'key' => new SoapVar($key, XSD_STRING, 'string', 'http://www.w3.org/2001/XMLSchema'),
                'value' => new SoapVar($value, XSD_STRING, 'string', 'http://www.w3.org/2001/XMLSchema')
            );
        }

        return array_values($parameters);
    }

}
