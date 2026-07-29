<?php

namespace App\Enums;

enum Sentiment: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Neutral = 'neutral';
    case Unknown = 'unknown';
}
