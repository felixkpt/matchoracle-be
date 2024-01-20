<?php

namespace App\Repositories\BettingTips;

use App\Repositories\BettingTips\Core\TipsListForCore;
use App\Repositories\BettingTips\Source\TipsListForSource;

class BettingTipsFactory
{
    public static function create($predictionTypeId, $tipType)
    {
        if ($predictionTypeId == 1) {
            return self::createForCore(new TipsListForCore(), $tipType);
        } elseif ($predictionTypeId == 2) {
            return self::createForSource(new TipsListForSource(), $tipType);
        }

        abort(404, 'Invalid mode specified');
    }

    protected static function createForSource(TipsListForSource $tipsList, $tipType)
    {
        $tipClasses = $tipsList->tipClasses;
        return self::instantiateTipClass($tipClasses, $tipType);
    }

    protected static function createForCore(TipsListForCore $tipsList, $tipType)
    {
        $tipClasses = $tipsList->tipClasses;
        return self::instantiateTipClass($tipClasses, $tipType);
    }

    protected static function instantiateTipClass($tipClasses, $tipType)
    {
        if (!isset($tipClasses[$tipType])) {
            abort(404, 'Tip type not found');
        }

        $className = $tipClasses[$tipType];

        if (class_exists($className)) {
            return new $className();
        }

        throw new \InvalidArgumentException("Invalid betting tips type: $tipType");
    }
}
