<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| JWT is INTENTIONALLY DISABLED
|--------------------------------------------------------------------------
|
| RentFlow uses Laravel sessions as its ONLY authentication mechanism. The
| legacy JWT bridge (LegacyJwt + AuthenticateLegacyJwt middleware, signed with
| JWT_SECRET) was REMOVED because it accepted an empty HS256 key — hash_hmac()
| produces a verifiable MAC with a blank key, so anyone could forge a token and
| impersonate any owner.
|
| This file returns an empty array so that if a JWT package is ever installed
| or config/jwt.php is accidentally restored, there is no secret configured and
| no token can be issued or verified. Sessions are the only way in.
|
| DO NOT re-enable JWT. If token-based auth is ever needed again, use signed
| URLs or opaque random tokens stored in the database — never self-contained
| JWTs with client-trusted payloads.
*/

return [];
