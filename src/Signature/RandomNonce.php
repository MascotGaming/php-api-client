<?php

namespace mascotgaming\mascot\api\client\Signature;

use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;

class RandomNonce implements Nonce
{
    public function next()
    {
        $nonce = new BigInteger(Random::string(8), 256);

        return $nonce->toString();
    }
}