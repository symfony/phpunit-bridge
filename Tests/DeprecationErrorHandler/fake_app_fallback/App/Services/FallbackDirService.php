<?php

namespace App\Services;

final class FallbackDirService
{
    public function selfDeprecation(): void
    {
        @trigger_error('Since FallbackApp 1.0: selfDeprecation is deprecated.', \E_USER_DEPRECATED);
    }
}
