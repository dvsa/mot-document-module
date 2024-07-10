<?php

namespace DvsaReport\Model;

class ReportNames
{
    public const VT20 = 'MOT/VT20.pdf';
    public const VT20W = 'MOT/VT20W.pdf';
    public const VT30 = 'MOT/VT30.pdf';
    public const VT30W = 'MOT/VT30W.pdf';

    public const VT32VE = "MOT/VT32VE.pdf";
    public const VT32VEW = "MOT/VT32VEW.pdf";
    public const EU_VT32VE = "MOT/EU_VT32VE.pdf";
    public const EU_VT32VEW = "MOT/EU_VT32VEW.pdf";

    public const PRS = 'MOT/PRS.pdf';
    public const PRSW = 'MOT/PRSW.pdf';

    /**
     * @return string[]
     */
    public static function getAll(): array
    {
        return [
            self::VT20,
            self::VT20W,
            self::VT30,
            self::VT30W,
            self::VT32VE,
            self::VT32VEW,
            self::EU_VT32VE,
            self::EU_VT32VEW,
            self::PRS,
            self::PRSW,
        ];
    }
}
