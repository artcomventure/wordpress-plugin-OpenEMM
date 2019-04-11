<?php

// simple en/decrypt class
// thx to https://nazmulahsan.me/simple-two-way-function-encrypt-decrypt-string/
class OpenEMMCrypt {
	const METHOD = 'AES-256-CBC';
	const KEY = 'open';
	const IV = 'emm';

	public function encrypt( $data, $key = self::KEY, $iv = self::IV ) {
		return base64_encode( openssl_encrypt( gzdeflate( serialize( $data ), 9 ), self::METHOD, $this->getKey( $key ), 0, $this->getIv( $iv ) ) );
	}

	public function decrypt( $string, $key = self::KEY, $iv = self::IV ) {
		return unserialize( gzinflate( openssl_decrypt( base64_decode( $string ), self::METHOD, $this->getKey( $key ), 0, $this->getIv( $iv ) ) ) );
	}

	private function getKey( $key ) {
		return hash( 'sha256', $key );
	}

	private function getIv( $iv ) {
		return substr( hash( 'sha256', $iv ), 0, 16 );
	}
}
