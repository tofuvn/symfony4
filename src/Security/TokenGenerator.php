<?php


namespace App\Security;


class TokenGenerator
{
    private const ALPHABET = "qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM123456789";

    public function getRandomSecureToken(int $length): string
    {
        $maxNumber = strlen(self::ALPHABET);

        $token = '';

        for($i = 0; $i< $length; $i++ ) {
            try {
                $token .= self::ALPHABET[random_int(0, $maxNumber - 1)];
            } catch (\Exception $e) {
            }
        }

        return $token;
    }

}