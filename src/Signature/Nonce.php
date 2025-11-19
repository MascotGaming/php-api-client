<?php

namespace mascotgaming\mascot\api\client\Signature;

/**
 * Interface Nonce
 *
 * Defines a common interface for generating unique nonces.
 */
interface Nonce
{
	/**
	 * Returns the next unique nonce value.
	 *
	 * @return string A unique numeric string (64-bit safe)
	 */
	public function next();
}
