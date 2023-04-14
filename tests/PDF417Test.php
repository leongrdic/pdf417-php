<?php

namespace Le\PDF417\Tests;

use Le\PDF417\PDF417;
use PHPUnit\Framework\TestCase;

class PDF417Test extends TestCase
{
    public function testDefaultsAndAccessors()
    {
        $cols = 20;
        $secLev = 6;

        $pdf = new PDF417();
        $this->assertSame($pdf::DEFAULT_COLUMNS, $pdf->getColumns());
        $this->assertSame($pdf::DEFAULT_SECURITY_LEVEL, $pdf->getSecurityLevel());

        $pdf->setColumns($cols);
        $this->assertSame($cols, $pdf->getColumns());

        $pdf->setSecurityLevel($secLev);
        $this->assertSame($secLev, $pdf->getSecurityLevel());
    }

    public function testInvalidColumns1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Column count must be numeric. Given: foo');
        $pdf = new PDF417();
        $pdf->setColumns('foo');
    }

    public function testInvalidColumns2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Column count must be between 1 and 30. Given: 1000');
        $pdf = new PDF417();
        $pdf->setColumns(1000);
    }

    public function testInvalidSecurityLevel1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Security level must be numeric. Given: foo');
        $pdf = new PDF417();
        $pdf->setSecurityLevel('foo');
    }

    public function testInvalidSecurityLevel2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Security level must be between 0 and 8. Given: 1000');
        $pdf = new PDF417();
        $pdf->setSecurityLevel(1000);
    }

    /** An end-to-end test. */
    public function testEncode()
    {
        $data = "HRVHUB30\nHRK\n000000010000000\nMarko Markić\nZagrebačka cesta 1\n10000 Zagreb\nTvrtka d.o.o.\nZagrebačka avenija 1\n10000 Zagreb\nHR1210010051863000160\n00\nHR123456\nCOST\nOpis placanja\n";

        $pdf = new PDF417();
        $barcodeData = $pdf->encode($data);

        $this->assertInstanceOf(\Le\PDF417\BarcodeData::class, $barcodeData);

        $expectedCWs = [142,227,637,601,902,130,900,865,479,227,328,765,902,1,624,142,113,522,200,900,865,479,387,17,314,808,852,810,520,269,901,196,135,900,865,479,777,6,514,30,901,196,141,900,820,26,64,559,26,902,11,900,865,479,902,122,200,900,805,810,197,121,865,479,597,647,580,26,118,537,448,537,448,535,479,777,6,514,30,901,196,141,900,820,26,21,133,249,26,902,11,900,865,479,902,122,200,900,805,810,197,121,865,479,227,902,21,84,225,369,446,822,360,900,865,479,902,100,900,865,479,227,902,1,348,256,900,865,479,74,559,865,479,447,458,566,461,2,13,270,865,479,46,872,436,580,181,446,308,867];

        $this->assertSame($expectedCWs, $barcodeData->codeWords);

        $expectedCodes = [
            [130728,108640,82050,93980,67848,99590,81384,82192,128318,260649],
            [130728,128280,97968,81084,101252,127694,75652,113982,125456,260649],
            [130728,86496,69396,120312,66846,104188,106814,96800,108792,260649],
            [130728,107712,93248,68708,73160,96008,127838,125758,119520,260649],
            [130728,110096,103300,99048,73210,99044,124646,129868,110088,260649],
            [130728,125892,106418,75512,76016,102290,66382,67960,125890,260649],
            [130728,108478,113892,108736,122060,110460,66594,124062,85560,260649],
            [130728,125248,125166,97968,123028,120324,90012,78588,129628,260649],
            [130728,129634,129766,69396,129744,102290,66382,67960,85116,260649],
            [130728,83740,81384,106974,93248,68708,66880,66820,107452,260649],
            [130728,108304,118902,125088,81084,101252,113634,94588,108296,260649],
            [130728,129588,71772,129766,82750,114076,77902,114076,83704,260649],
            [130728,106648,113472,78212,96008,113892,108736,122060,106672,260649],
            [130728,125064,110144,114524,106728,125166,97968,123028,125060,260649],
            [130728,82206,129766,88188,103672,91966,129766,69396,82366,260649],
            [130728,76992,86048,68708,73160,96008,81384,106974,104160,260649],
            [130728,83842,82408,97968,123062,99044,118902,125088,107502,260649],
            [130728,124392,66382,67960,121150,69396,88188,108516,127734,260649],
            [130728,111632,94008,101232,117936,99972,116024,68708,111648,260649],
            [130728,82924,81084,101252,97944,108292,97968,81084,124968,260649],
            [130728,74992,67960,121150,69396,120312,124666,102906,103036,260649],
            [130728,75288,68708,73160,96008,107104,117798,73160,112440,260649],
            [130728,124176,101252,128522,127308,95870,123524,125504,124168,260649],
            [130728,126450,110072,90940,66382,67960,120806,69456,117236,260649],
            [130728,111456,128860,94700,128670,117936,110982,66626,102752,260649]
        ];

        $this->assertSame($expectedCodes, $barcodeData->codes);
    }
}
