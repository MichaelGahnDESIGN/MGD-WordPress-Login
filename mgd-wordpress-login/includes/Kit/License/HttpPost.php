<?php

declare(strict_types=1);

namespace Mgd\WordPressLogin\Kit\License;

use Mgd\WordPressLogin\Kit\Install\Response;

// POST mit Body für die Lizenzprüfung. Install-Client Transport kann nur Kopfzeilen, deshalb eigenes Interface im Kit
interface HttpPost
{
    /** @param array<string,string> $headers */
    public function post(string $url, array $headers, string $body): Response;
}
