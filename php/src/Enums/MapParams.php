<?php

namespace App\Enums;

enum MapParams: string
{
    case ContentCode = 'content_code';
    case ContentType = 'content_type';
    case HideBlockedMaps = 'hide_blocked_maps';
    case Layer = 'layer';
    case Page = 'page';
    case Size = 'size';
}
