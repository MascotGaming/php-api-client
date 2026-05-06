<?php

namespace mascotgaming\mascot\api\client\Signature;

use phpseclib3\Crypt\Random;
use phpseclib3\Math\BigInteger;

class RandomNonce implements Nonce
{
    public function next()
    {
        $nonce = new BigInteger(Random::string(8), 256);

        return $nonce->toString();
    }
}