<?php

namespace Progi1984\PhpDatabase\Reader;

use Progi1984\PhpDatabase\PhpDatabase;

class Sqlite extends AbstractReader
{
    const SYS_INDEX = 2;
    const SYS_TABLE = 5;
    const USR_INDEX = 10;
    const USR_TABLE = 13;

    /**
     * @var string
     */
    protected $data;
    /**
     * @var int
     */
    protected $pos = 0;
    /**
     * @var int
     */
    protected $dbPageSize = 0;
    /**
     * @var int
     */
    protected $bTreeNumCells;
    /**
     * @var int
     */
    protected $bTreePageType;
    /**
     * @var array
     */
    protected $bTreeCellPointer = array();

    /**
     * @param string $filename
     * @return PhpDatabase
     * @throws \Exception
     */
    public function readFile($filename) {
        if (!is_readable($filename)) {
            throw new \Exception('File '.$filename.' is not readable');
        }

        $this->data = file_get_contents($filename);
        hex_dump($this->data);

        // Page 1
        $this->loadHeader();
        $numPage = 1;
        echo '<br /><br /><br /><br />';
        $data = $this->getPage($numPage);
        do {
            hex_dump($data);
            $this->loadBTree($data, $numPage);

            $numPage++;

            $data = $this->getPage($numPage);
            die();
        } while (!empty($data));


        $database = new PhpDatabase();
        return $database;
    }

    protected function loadHeader()
    {
        $headerString = '';
        for($this->pos = 0; $this->pos < 16 ; $this->pos++) {
            echo $this->getInt1d($this->data, $this->pos).'<br />';
            $headerString .= chr($this->getInt1d($this->data, $this->pos));
        }

        $this->dbPageSize = $this->getInt2d($this->data, $this->pos);
        echo '$dbPageSize : '.$this->dbPageSize.'<br/>';
        $this->pos +=2;

        $fileFormatWriteVersion = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$fileFormatWriteVersion : '.$fileFormatWriteVersion.'<br/>';

        $fileFormatReadVersion = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$fileFormatReadVersion : '.$fileFormatReadVersion.'<br/>';

        $bytesUnused = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$bytesUnused : '.$bytesUnused.'<br/>';

        $maximumEmbeddedPayloadFraction = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$maximumEmbeddedPayloadFraction : '.$maximumEmbeddedPayloadFraction.'<br/>';

        $minimumEmbeddedPayloadFraction = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$minimumEmbeddedPayloadFraction : '.$minimumEmbeddedPayloadFraction.'<br/>';

        $leafPayloadFraction = $this->getInt1d($this->data, $this->pos);
        $this->pos++;
        echo '$leafPayloadFraction : '.$leafPayloadFraction.'<br/>';

        $fileChangeCounter = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$fileChangeCounter : '.$fileChangeCounter.'<br/>';

        $sizeOfDatabaseFileInPages = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$sizeOfDatabaseFileInPages : '.$sizeOfDatabaseFileInPages.'<br/>';

        $pageNumberOfFirstFreelistTrunkPage = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$pageNumberOfFirstFreelistTrunkPage : '.$pageNumberOfFirstFreelistTrunkPage.'<br/>';

        $totalNumberFreeListPages = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$totalNumberFreeListPages : '.$totalNumberFreeListPages.'<br/>';

        $schemaCookie = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$schemaCookie : '.$schemaCookie.'<br/>';

        $schemaFormatNumber = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$schemaFormatNumber : '.$schemaFormatNumber.'<br/>';

        $defaultPageCacheSize = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$defaultPageCacheSize : '.$defaultPageCacheSize.'<br/>';

        // The page number of the largest root b-tree page when in auto-vacuum or incremental-vacuum modes, or zero otherwise
        $this->pos += 4;

        $databaseTextEncoding = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$databaseTextEncoding (1:UTF-8, 2: UTF-16LE, 3: UTF-16BE) : '.$databaseTextEncoding.'<br/>';

        $userVersion = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$userVersion : '.$userVersion.'<br/>';

        $incrementalVacuumMode = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$incrementalVacuumMode : '.$incrementalVacuumMode.'<br/>';

        $applicationId = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$applicationId : '.$applicationId.'<br/>';

        // Reserved for expansion. Must be zero.
        $this->pos += 20;

        $checkIntegrity = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$checkIntegrity (must be equal to $fileChangeCounter) : '.$checkIntegrity.'<br/>';

        $sqliteVersion = $this->getInt4d($this->data, $this->pos);
        $this->pos += 4;
        echo '$sqliteVersion (must be equal to $fileChangeCounter) : '.$sqliteVersion.'<br/>';
    }

    /**
     * @param string $data
     * @param int $numPage
     */
    protected function loadBTree($data, $numPage)
    {
        // Page Header
        if ($numPage == 1) {
            $this->pos = 100;
        }

        $this->bTreePageType = $this->getInt1d($data, $this->pos);
        $this->pos += 1;
        echo '$bTreePageType (2 : sysIndex ; 5 : sysTable ; 10 : userIndex ; 13 : userTable) : '.$this->bTreePageType.'<br/>';

        $startFreeBlock = $this->getInt2d($data, $this->pos);
        $this->pos += 2;
        echo '$startFreeBlock : '.$startFreeBlock.'<br/>';

        $this->bTreeNumCells = $this->getInt2d($data, $this->pos);
        $this->pos += 2;
        echo '$numCells : '.$this->bTreeNumCells.'<br/>';

        $startCellContent = $this->getInt2d($data, $this->pos);
        $this->pos += 2;
        echo '$startCellContent : '.dechex($startCellContent).'<br/>';

        $numFragmentedFreeBytes = $this->getInt1d($data, $this->pos);
        $this->pos += 1;
        echo '$numFragmentedFreeBytes : '.$numFragmentedFreeBytes.'<br/>';

        if ($this->bTreePageType == self::SYS_TABLE) {
            $rightMostPointer = $this->getInt4d($data, $this->pos);
            $this->pos += 4;
            echo '$rightMostPointer : '.$rightMostPointer.'<br/>';
        }

        // CellPointer
        $bTreeCellPointer = array();
        for ($inc = 0 ; $inc < $this->bTreeNumCells ; $inc++) {
            $bTreeCellPointer[] = $this->getInt2d($data, $this->pos);
            $this->pos += 2;
        }
        array_reverse($bTreeCellPointer);

        foreach ($bTreeCellPointer as $cellPtr) {
            switch ($this->bTreePageType) {
                case self::SYS_INDEX:
                    echo __LINE__;
//                die();
                    break;
                case self::SYS_TABLE:
                    $pgNumber = self::getInt4d($data, $cellPtr);
                    $cellPtr += 4;
                    echo '$pgNumber : '.$pgNumber.'<br/>';

                    $intKey = $this->getVarInt($data, $cellPtr);
                    echo '$intKey : '.$intKey.'<br/>';

                    hex_dump(substr($data, $cellPtr, 5));

                    $pg = $this->getPage($pgNumber);
                    $this->pos = 0;
                    $this->loadBTree($pg, $pgNumber);
                    hex_dump($pg);

                    die();
                    break;
                case self::USR_INDEX:
                    echo __LINE__;
//                die();
                    break;
                case self::USR_TABLE:
                    $lengthPayload = $this->getVarInt($data, $cellPtr);
                    echo '$lengthPayload : '.$lengthPayload.'<br/>';
                    $rowId = $this->getVarInt($data, $cellPtr);
                    echo '$rowId : '.$rowId.'<br/>';
                    $payloadHeader = $this->getInt1d($data, $cellPtr);
                    $cellPtr++;
                    echo '$payloadHeader : '.$payloadHeader.'<br/>';
                    $payload = 0;
                    $arrayVarint = array();
                    for ($inc = 1 ; $inc < $payloadHeader ; $inc++) {
                        $varint = $this->getInt1d($data, $cellPtr);
                        $cellPtr++;
                        echo '$varint : '.$varint.'<br/>';
                        if ($varint % 2 == 0 && $varint >= 12) {
                            $payload += (($varint - 12) / 2);
                        } else {
                            if ($varint % 2 == 1 && $varint >= 13) {
                                $payload += (($varint - 13) / 2);
                            } else {
                                if ($varint >= 1 && $varint <= 8) {
                                    $payload += $varint;
                                }
                            }
                        }
                        $arrayVarint[] = $varint;
                    }
                    $payload += $payloadHeader;
                    echo '$payload : '.$payload.'<br/>';

                    var_dump($arrayVarint);

                    foreach ($arrayVarint as $varint) {
                        if ($varint % 2 == 0 && $varint >= 12) {
                            // BLOB
                            $length = (($varint - 12) / 2);
                        } else {
                            if ($varint % 2 == 1 && $varint >= 13) {
                                // TEXT
                                $length = (($varint - 13) / 2);
                                for ($incChar = 0 ; $incChar < $length ; $incChar++) {
                                    echo chr($this->getInt1d($data, $cellPtr + $incChar));
                                }
                            } else {
                                if ($varint >= 1 && $varint <= 8) {
                                    // INT
                                }
                            }
                        }

                        $cellPtr += $varint;
                    }
//                die();
                    break;
            }
        }
    }

    protected function getPage($numPage)
    {
        return substr($this->data, (($numPage - 1) * $this->dbPageSize), $this->dbPageSize);
    }

    protected function getInt1d($data, $pos)
    {
        return ord($data[$pos]);
    }

    protected function getInt2d($data, $pos)
    {
        $dataUnPack = unpack('n',  substr($data, $pos, 2));
        return reset($dataUnPack);
    }

    protected function getInt4d($data, $pos)
    {
        $dataUnPack = unpack('N',  substr($data, $pos, 4));
        return reset($dataUnPack);
    }

    protected function getVarInt($data, &$pos)
    {
        $original = '';
        $varint = $shift = 0;
        do {
            $original .= $data[$pos];
            $byte = ord($data[$pos]);
            $varint |= ($byte & 0x7f) << $shift++ * 7;
            $pos +=1;
        } while ($byte > 0x7f);

        return $varint;
    }
}