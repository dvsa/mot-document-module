<?php

namespace DvsaReport\Model;


class ReportNames
{
    const VT20 = 'MOT/VT20.pdf';
    const VT20W = 'MOT/VT20W.pdf';
    const VT30 = 'MOT/VT30.pdf';
    const VT30W = 'MOT/VT30W.pdf';

    const VT32VE = "MOT/VT32VE.pdf";
    const VT32VEW = "MOT/VT32VEW.pdf";
    const EU_VT32VE = "MOT/EU_VT32VE.pdf";
    const EU_VT32VEW = "MOT/EU_VT32VEW.pdf";

    const PRS = 'MOT/PRS.pdf';
    const PRSW = 'MOT/PRSW.pdf';

    public static function getAll()
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