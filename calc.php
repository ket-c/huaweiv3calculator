<?php

/*****************************************************
 *                                                   *
 *         HUAWEI CODE CALCULATOR "ALGO V1+V2+V3"    *
 *                                                   *
 *   PHP code by Sergey/MKL  2016                    *
 *   (based on leaked Delphi source and Russian      *
 *   source)                                         *
 *                                                   *
 *       * Mod by Kwaku (WA)
 *                                                   *
 *****************************************************

 V2.1 code
 + Fixed for 32 bit CPU integer overflow

 Testdata
 IMEI:   968480435684491
 NCK V2: 23823444

 V3.0 code
 + Added V3 (201) algo variations, Huawei custom fake CRC32

 V3.1 code
 + Bugfix algo5 shift

 V3.2 code
 + Bugfix algo 3  dfkdkfllekkodk hash

 V3.3 code
 + Bugfix algo 201 Subtype 2016

*/


function calcv1($imei, $type)
{
	$const = substr(md5($type ? "e630upgrade" : "hwe620datacard"), 8, 16);
	$magic = pack("H*", md5($imei.$const));
	$code = array();
	for($i = 0; $i < 4; $i++)
		$code[$i]=ord($magic[3-$i]^$magic[7-$i]^$magic[15-$i]^$magic[11-$i]);

	$nck = (($code[3] << 0x18 | $code[2] << 0x10 | $code[1] << 0x08 | $code[0]) & 0x1FFFFFF) | 0x2000000;
	return $nck;
}

function calcv2($imei)
{
	$algo_id = algo_selector($imei);
	switch ($algo_id)
	{
	case 0:
		return algo0($imei);
	case 1:
		return algo1($imei);
	case 2:
		return algo2($imei);
	case 3:
		return algo3($imei);
	case 4:
		return algo4($imei);
	case 5:
		return algo5($imei);
	case 6:
		return algo6($imei);
	default:
		return 0;
	}
}

function calcv3($imei)
{
	$algo_id = algo_selector($imei, 201);
	switch ($algo_id)
	{
	case 0:
		return algo0($imei, 201);
	case 1:
		return algo1($imei, 201);
	case 2:
		return algo2($imei, 201);
	case 3:
		return algo3($imei, 201);
	case 4:
		return algo5($imei, 2015);
	case 5:
		return algo5($imei, 2016);
	case 6:
		return algo6($imei, 201);
	default:
		return 0;
	}
}

function algo_selector($imei, $mode = 0)
{
	$x = 0;
	for ($i = 0; $i < 15; $i++)
		if($mode == 201)
			$x+= ((ord($imei[$i]) + $i + 1) * ord($imei[$i])) * (ord($imei[$i]) + 313);
		else
			$x+= (ord($imei[$i]) + $i + 1) * ($i + 1);
	return ($x % 7);
}

function algo0($imei, $mode = 0)
{
	$table_v2 = array(
	0x001966A9, 0x0021058F, 0x002AEDA9, 0x0037CE91, 0x00488C9F, 0x005E507D,
	0x007A9BE5, 0x009F644B, 0x00CF35A1, 0x010D5F55, 0x015E2F25, 0x01C73D6B,
	0x024FCFDD, 0x03015B47, 0x03E829E9);

	$table_201 = array(
	0x006E9C2A, 0x03CA2B3C, 0x001080DC, 0x30855EE, 0x03D3283A, 0x02F4F85A,
	0x01F8808E, 0x03147D10, 0x034BBBB5, 0x29EEADD, 0x02318616, 0x050F3ADC,
	0x00D11F38, 0x02123BD2, 0x04276C86, 0x355CAAD);

	$code = array();
	$s = 0;
	for ($i = 0; $i < 15; $i++)
		$s+= ($imei[$i] + 0x30) * ($mode == 201 ? $table_201[$i] : $table_v2[$i]);
	for ($i = 0; $i <= 7; $i++)
	{
		$code[$i] = ($s >> 4 * $i & 0x0F) % 10;
	}
	if ($code[0] == 0)
		$code[0] = 1;
	$nck = implode("", $code);
	return $nck;
}

function fake_crc32_huawei($data)
{

	$custom_table = array(
	0x00000000, 0x77073096, 0xee0e612c, 0x990951ba, 0x076dc419, 0x196c3671, 0x6e6b06e7, 0xfed41b76, 
	0x89d32be0, 0x10da7a5a, 0xfbd44c65, 0x4db26158, 0x3ab551ce, 0xa3bc0074, 0xd4bb30e2, 0x4adfa541, 
	0x3dd895d7, 0xa4d1c46d, 0xd3d6f4fb, 0x4369e96a, 0xd6d6a3e8, 0xa1d1937e, 0x38d8c2c4, 0x4fdff252, 
	0xd1bb67f1, 0xa6bc5767, 0x3fb506dd, 0x48b2364b, 0xd80d2bda, 0xaf0a1b4c, 0x36034af6, 0x41047a60, 
	0xdf60efc3, 0xa867df55, 0x316e8eef, 0x90bf1d91, 0x1db71064, 0x6ab020f2, 0xf3b97148, 0x84be41de, 
	0x1adad47d, 0x6ddde4eb, 0xf4d4b551, 0x83d385c7, 0x136c9856, 0xfa0f3d63, 0x8d080df5, 0x3b6e20c8, 
	0x4c69105e, 0xd56041e4, 0xa2677172, 0x3c03e4d1, 0x4b04d447, 0xd20d85fd, 0xa50ab56b, 0x646ba8c0, 
	0xfd62f97a, 0x8a65c9ec, 0x14015c4f, 0x63066cd9, 0x45df5c75, 0xdcd60dcf, 0xabd13d59, 0x26d930ac, 
	0x51de003a, 0xc8d75180, 0xbfd06116, 0x21b4f4b5, 0x56b3c423, 0xcfba9599, 0x706af48f, 0xe963a535, 
	0x9e6495a3, 0x0edb8832, 0x79dcb8a4, 0xe0d5e91e, 0x97d2d988, 0x09b64c2b, 0x7eb17cbd, 0xe7b82d07, 
	0x35b5a8fa, 0x42b2986c, 0xdbbbc9d6, 0xacbcf940, 0x32d86ce3, 0xb8bda50f, 0x2802b89e, 0x5f058808, 
	0xc60cd9b2, 0xb10be924, 0x2f6f7c87, 0x58684c11, 0xc1611dab, 0xb6662d3d, 0x76dc4190, 0x4969474d, 
	0x3e6e77db, 0xaed16a4a, 0xd9d65adc, 0x40df0b66, 0x37d83bf0, 0xa9bcae53, 0xdebb9ec5, 0x47b2cf7f, 
	0x30b5ffe9, 0xbdbdf21c, 0xcabac28a, 0x53b39330, 0x24b4a3a6, 0xbad03605, 0x03b6e20c, 0x74b1d29a, 
	0xead54739, 0x9dd277af, 0x04db2615, 0xe10e9818, 0x7f6a0dbb, 0x086d3d2d, 0x91646c97, 0xe6635c01, 
	0x6b6b51f4, 0x1c6c6162, 0x856530d8, 0xf262004e, 0x6c0695ed, 0x1b01a57b, 0x8208f4c1, 0xf50fc457, 
	0x65b0d9c6, 0x12b7e950, 0x8bbeb8ea, 0xfcb9887c, 0x62dd1ddf, 0x15da2d49, 0x8cd37cf3, 0xe40ecf0b, 
	0x9309ff9d, 0x0a00ae27, 0x7d079eb1, 0xf00f9344, 0x4669be79, 0xcb61b38c, 0xbc66831a, 0x256fd2a0, 
	0x5268e236, 0xcc0c7795, 0xbb0b4703, 0x220216b9, 0x5505262f, 0xc5ba3bbe, 0x68ddb3f8, 0x1fda836e, 
	0x81be16cd, 0xf6b9265b, 0x6fb077e1, 0x18b74777, 0x88085ae6, 0xff0f6a70, 0x66063bca, 0x11010b5c, 
	0x8f659eff, 0xf862ae69, 0x616bffd3, 0x166ccf45, 0xa00ae278, 0xb2bd0b28, 0x2bb45a92, 0x5cb36a04, 
	0xc2d7ffa7, 0xb5d0cf31, 0x2cd99e8b, 0x5bdeae1d, 0x9b64c2b0, 0xec63f226, 0x756aa39c, 0x026d930a, 
	0x9c0906a9, 0xeb0e363f, 0x72076785, 0x05005713, 0x346ed9fc, 0xad678846, 0xda60b8d0, 0x44042d73, 
	0x33031de5, 0xaa0a4c5f, 0xdd0d7cc9, 0x5005713c, 0x270241aa, 0xbe0b1010, 0x01db7106, 0x98d220bc, 
	0xefd5102a, 0x71b18589, 0x06b6b51f, 0x9fbfe4a5, 0xe8b8d433, 0x7807c9a2, 0x0f00f934, 0x9609a88e, 
	0xc90c2086, 0x5768b525, 0x206f85b3, 0xb966d409, 0xce61e49f, 0x5edef90e, 0x29d9c998, 0xb0d09822, 
	0xc7d7a8b4, 0x59b33d17, 0xcdd70693, 0x54de5729, 0x23d967bf, 0xb3667a2e, 0xc4614ab8, 0x5d681b02, 
	0x2a6f2b94, 0xb40bbe37, 0xc30c8ea1, 0x5a05df1b, 0x2eb40d81, 0xb7bd5c3b, 0xc0ba6cad, 0xedb88320, 
	0x9abfb3b6, 0x73dc1683, 0xe3630b12, 0x94643b84, 0x0d6d6a3e, 0x7a6a5aa8, 0x67dd4acc, 0xf9b9df6f, 
	0x8ebeeff9, 0x17b7be43, 0x60b08ed5, 0x8708a3d2, 0x1e01f268, 0x6906c2fe, 0xf762575d, 0x806567cb, 
	0x95bf4a82, 0xe2b87a14, 0x7bb12bae, 0x0cb61b38, 0x92d28e9b, 0xe5d5be0d, 0x7cdcefb7, 0x0bdbdf21, 
	0x86d3d2d4, 0xf1d4e242, 0xd70dd2ee, 0x4e048354, 0x3903b3c2, 0xa7672661, 0xd06016f7, 0x2d02ef8d);
 
	$p = 0;
	$s = strlen($data);
	$crc = 0xFFFFFFFF;

	for($i = 0; $i < strlen($data); $i++)
		$crc = ($custom_table[ ($crc ^ ord($data[$p++])) & 0xFF ] ^ (($crc >> 8) & 0xFFFFFF) );
 
	return $crc ^ 0xFFFFFFFF;
}

function algo1($imei, $mode = 0)
{

	$crc = ($mode == 201 ? fake_crc32_huawei($imei) : crc32($imei) );
	if ($crc & 0x80000000)
	{
		$crc*= - 1;
		$crc&= 0xFFFFFFFF;
	}

	$nck = str_pad(substr($crc, -8) , 8, "9", STR_PAD_LEFT);
	if ($nck[0] == '0')
		$nck[0] = '9';
	return $nck;
}

function algo2($imei, $mode = 0)
{
	$code = substr(pack("H*", md5($imei)), ($mode == 201 ? 5 : 0), 8);
	$a = ord($code[0]) % 10;
	$code[0] = ($a != 0) ? chr($a) : chr(5);
	$nck = "";
	for ($i = 0; $i < 8; $i++)
	{
		if ($code[$i] > 0 && $code[$i] < 9)
			$nck.= $code[$i];
		else
			$nck.= chr((ord($code[$i]) % 10) + 0x30);
	}

	return $nck;
}

function algo3($imei, $mode)
{
	$const = pack("H*", md5($mode == 201 ? "dfkdkfllekkodk" : "hwideadatacard"));
	$md5bin = pack("H*", md5($imei . $const));
	$code = array();
	for ($i = 0; $i < 4; $i++)
		$code[$i] = ord($md5bin[$i]) ^ ord($md5bin[$i + 4]) ^ ord($md5bin[$i + 8]) ^ ord($md5bin[$i + 12]);
	$nck = (($code[0] << 0x18 | $code[1] << 0x10 | $code[2] << 0x08 | $code[3]) & 0x1FFFFFF) | 0x2000000;
	return $nck;
}

function algo4($imei)
{
	$nck = "";
	$magic = "5739146280098765432112345678905";
	$code = str_split($imei . "Z");
	for ($i = 0; $i < 8; $i++)
		$code[$i] = (ord($code[$i]) ^ ord($code[$i + 8]));
	for ($i = 0; $i < 8; $i++)
		$code[$i] = $magic[($code[$i] & 0x0F) + ($code[$i] >> 4) ];
	if ($code[0] == 0)
	{
		for ($i = 0; $i < 8; $i++)
			if ($code[$i] <> 0) break;

		$code[0] = $i;
	}

	$nck = implode("", $code);
	$nck = substr($nck, 0, 8);
	return $nck;
}

function algo5($imei, $mode)
{
	$sha1bin = pack("H*", sha1($imei));

	$o = ($mode == 2015 ? 4 : 0);
	$o = ($mode == 2016 ? 8 : 0);

	$tmp = unpack('N', substr($sha1bin, $o, 4));		// Big endian
	$a = $tmp[1];
	$tmp = unpack('N', substr($sha1bin, $o + 4, 4));	// Big endian
	$b = $tmp[1];

	if ($a < 0)		// Raspberry PI fix
		$a+= 0x100000000;
	if ($b < 0)
		$b+= 0x100000000;

	$nck = substr($a . $b, 0, 8);
	return $nck;
}


function algo6($imei, $mode = 0)
{
	$magic =    array( 0x01, 0x01, 0x02, 0x03, 0x05, 0x08, 0x0D, 0x15, 0x22, 0x37, 0x59, 0x90);
	$magic201 = array( 0x0B, 0x0D, 0x11, 0x13, 0x17, 0x1D, 0x1F, 0x25, 0x29, 0x2B, 0x3B, 0x61);
	$buffer = array_fill(0, 0x7F, 0x00);
	$dest_buf = array();
	$code = array(); //dst_buf

	for ($i = 0; $i < 15; $i++)
		$buffer[$i] = ((ord($imei[$i]) >> ($i % 3 + 2)) | (ord($imei[$i]) << (6 - $i % 3))) & 0xFF;
	
	$sum_1 = 0;

	for ($i = 0; $i < 7; $i++)
		$sum_1+= ($buffer[$i] << 8) + $buffer[14 - $i];

	$sum_1+= $buffer[8];

	for ($i = 0x0F, $j = 0; $i < 0x80; $i++, $j++)
	{
		$var_34 = intval($i / 12);
		$var_38 = ($i + $var_34) % 12;

		$R1 = $j % 0x0C;

		if ($var_34 < 2)
			$var_34+= $R1;
		else
			$var_34 = $R1 + ($var_34 * 13) - 24;

		$R0 = (0xFFFFFFFF - $buffer[$j ? $sum_1 % $j : $sum_1 % $i + 1]);
		$R0|= $buffer[$sum_1 % $i];
		$a = (($R0) | ($buffer[$var_34]) & ($mode == 201 ? $magic201[$var_38] : $magic[$var_38])) & 0xFF;
		$buffer[$i] = ($a & 0xFF);
	}

	$sum_2 = 0;

	for ($i = 0; $i < 7; $i++)
		$sum_2+= ((ord($imei[$i]) << 8) | ord($imei[$i + 1]));

	$sum_2 += ord($imei[14]);

	$tmp = implode("", array_map("chr", $buffer));
	$md5bin = pack("H*", md5($tmp));
	$idx = $sum_2 & 3;
	$tmp = unpack('V', substr($md5bin, $idx * 4, 4));
	$hash_unit = $tmp[1];

	if ($hash_unit < 0)	// Raspberry PI fix
		($hash_unit += 0x100000000) & 0xFFFFFFFF;

	$nck_idx = 0;

	for ($i = 0; $i < 16; $i++)
		if (is_numeric($md5bin[$i]))
			$dest_buf[$nck_idx++] = ($md5bin[$i]);

	$j = 0;
	while ($hash_unit)
	{
		$dest_buf[$nck_idx++] = substr($hash_unit, -1);
		$hash_unit = intval($hash_unit / 10);

		if ($hash_unit == 0 && $j == 0)
		{
			$j = 1;
			$hash_unit = substr($md5bin, (3 - $idx) * 4, 4);
			$tmp = unpack("V", $hash_unit);
			$hash_unit = $tmp[1];

			if ($hash_unit < 0)	// Raspberry PI fix
				($hash_unit += 0x100000000) & 0xFFFFFFFF;
		}

		if ($nck_idx == 8)
		{
			if ($dest_buf[0] == 0) $dest_buf[0] = (($sum_2 ? ord($md5bin[1]) : ord($md5bin[0])) & 7) + 1;
			return implode("", $dest_buf);
		}
	}
	return 0;
}

error_reporting(0);

if(isset($_POST["imei"]))
	$imei=trim($_POST["imei"]);
else
	$imei="";

echo "<html style='color:white;'><head></head><body style='color:white;'><h3><hr></h3><ul >";
if(!$imei || !is_numeric($imei) || strlen($imei) != 15)
{
	echo "Oups! Somthing went wrong. Invalid IMEI";
	die();
}

echo "IMEI: ".$imei."\n\n";

//echo "Algo V2:       ".algo_selector($imei)."\n";
//echo "Algo V3:       ".algo_selector($imei,201)."\n";
echo "<li>Unlock Code (V1): &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp     ".calcv1($imei, 0)."</li>"."\n";
echo "<li>Unlock Code (V2): &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp     ".calcv2($imei)."</li>"."   \n";

echo "<li>Unlock Code (V3/201): &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp     ".calcv3($imei, 201)."</li>"."\n";
/*echo "<strong style='text-decoration:underline; color:yellow;'>V3/ 201</strong> <br><li>Algo 0: &nbsp&nbsp ".algo0($imei, 201)."</li>"."   \n";
echo "<li>Algo 1: &nbsp&nbsp ".algo1($imei, 201)."</li>"."   \n";
echo "<li>Algo 2: &nbsp&nbsp ".algo2($imei, 201)."</li>"."   \n";
echo "<li>Algo 3: &nbsp&nbsp ".algo3($imei, 201)."</li>"."   \n";
echo "<li>Algo 4: &nbsp&nbsp ".algo4($imei, 201)."</li>"."   \n";
echo "<li>Algo 5 (2015): &nbsp&nbsp ".algo5($imei, 2015)."</li>"."   \n";
echo "<li>Algo 5 (2016): &nbsp&nbsp ".algo5($imei, 2016)."</li>"."   \n";
echo "<li>Algo 6: &nbsp&nbsp ".algo6($imei, 201)."</li>"."   \n"; */
echo "<li>Flash Code:    &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp        ".calcv1($imei, 1)."</li>"."\n";
echo "\n&copy KET-C ".date('Y')." </ul></body></html>\n";


?>
