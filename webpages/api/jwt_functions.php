<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// These functions provide support for handling JWTs

require_once('../vendor/autoload.php');

use Emarref\Jwt\Claim;

function validate_jwt_token($token) {
    
    $jwt = new Emarref\Jwt\Jwt();

    $algorithm = new Emarref\Jwt\Algorithm\Hs512(JWT_TOKEN_SIGNING_KEY);
    $encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
	$context = new Emarref\Jwt\Verification\Context($encryption);

	try {
		$deserialized = $jwt->deserialize($token);

		// annoyingly, the $jwt->verify function wants to verify a specific subject.
		// so let's just call the individual verifiers.
		$verifiers = [
            new Emarref\Jwt\Verification\EncryptionVerifier($context->getEncryption(),  new Emarref\Jwt\Encoding\Base64()),
            new Emarref\Jwt\Verification\ExpirationVerifier(),
            new Emarref\Jwt\Verification\NotBeforeVerifier(),
        ];

		foreach ($verifiers as $verifier) {
            $verifier->verify($deserialized);
        }
		return true;
	} catch (InvalidArgumentException $e) {
		return false;
	} catch (Emarref\Jwt\Exception\VerificationException $e) {
		return false;
	}
}

function create_jwt_token($badgeid, $name) {
    $token = new Emarref\Jwt\Token();

    // Standard claims are supported
    $token->addClaim(new Claim\Expiration(new DateTime('1 year')));
    $token->addClaim(new Claim\IssuedAt(new DateTime('now')));
    $token->addClaim(new Claim\Issuer(CON_NAME));
    $token->addClaim(new Claim\NotBefore(new DateTime('now')));
    $token->addClaim(new Claim\Subject($badgeid));
    $token->addClaim(new Claim\PublicClaim('name', $name));

    $jwt = new Emarref\Jwt\Jwt();

    $algorithm = new Emarref\Jwt\Algorithm\Hs512(JWT_TOKEN_SIGNING_KEY);
    $encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
    $serializedToken = $jwt->serialize($token, $encryption);


    return $serializedToken;
}

?>