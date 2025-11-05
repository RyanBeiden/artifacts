<?php

namespace App\Enums;

enum ResourceParams: string
{
    case Drop = 'drop';
    case MaxLevel = 'max_level';
    case MinLevel = 'min_level';
    case Skill = 'skill';
    case Page = 'page';
    case Size = 'size';
}
