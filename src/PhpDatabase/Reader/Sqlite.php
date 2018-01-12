<?php

namespace Progi1984\PhpDatabase\Reader;

use Progi1984\PhpDatabase\PhpDatabase;

class Sqlite extends AbstractReader
{
    /**
     * @var string
     */
    protected $data;
    /**
     * @var int
     */
    protected $pos = 0;

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
        $this->loadHeader();


        $database = new PhpDatabase();
        return $database;
    }

    protected function loadHeader()
    {
        $headerString = '';
        for($this->pos = 0; $this->pos < 16 ; $this->pos++) {
            echo ord($this->data[$this->pos]).'<br />';
            $headerString .= chr(ord($this->data[$this->pos]));
        }

        $dbPageSize = $this->getInt2d($this->pos);
        echo '$dbPageSize : '.$dbPageSize.'<br/>';
        $this->pos +=2;

        $fileFormatWriteVersion = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$fileFormatWriteVersion : '.$fileFormatWriteVersion.'<br/>';

        $fileFormatReadVersion = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$fileFormatReadVersion : '.$fileFormatReadVersion.'<br/>';

        $bytesUnused = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$bytesUnused : '.$bytesUnused.'<br/>';

        $maximumEmbeddedPayloadFraction = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$maximumEmbeddedPayloadFraction : '.$maximumEmbeddedPayloadFraction.'<br/>';

        $minimumEmbeddedPayloadFraction = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$minimumEmbeddedPayloadFraction : '.$minimumEmbeddedPayloadFraction.'<br/>';

        $leafPayloadFraction = ord($this->data[$this->pos]);
        $this->pos++;
        echo '$leafPayloadFraction : '.$leafPayloadFraction.'<br/>';

        $fileChangeCounter = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$fileChangeCounter : '.$fileChangeCounter.'<br/>';

        $sizeOfDatabaseFileInPages = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$sizeOfDatabaseFileInPages : '.$sizeOfDatabaseFileInPages.'<br/>';

        $pageNumberOfFirstFreelistTrunkPage = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$pageNumberOfFirstFreelistTrunkPage : '.$pageNumberOfFirstFreelistTrunkPage.'<br/>';

        $totalNumberFreeListPages = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$totalNumberFreeListPages : '.$totalNumberFreeListPages.'<br/>';

        $schemaCookie = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$schemaCookie : '.$schemaCookie.'<br/>';

        $schemaFormatNumber = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$schemaFormatNumber : '.$schemaFormatNumber.'<br/>';

        $defaultPageCacheSize = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$defaultPageCacheSize : '.$defaultPageCacheSize.'<br/>';

        // The page number of the largest root b-tree page when in auto-vacuum or incremental-vacuum modes, or zero otherwise
        $this->pos += 4;

        $databaseTextEncoding = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$databaseTextEncoding (1:UTF-8, 2: UTF-16LE, 3: UTF-16BE) : '.$databaseTextEncoding.'<br/>';

        $userVersion = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$userVersion : '.$userVersion.'<br/>';

        $incrementalVacuumMode = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$incrementalVacuumMode : '.$incrementalVacuumMode.'<br/>';

        $applicationId = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$applicationId : '.$applicationId.'<br/>';

        // Reserved for expansion. Must be zero.
        $this->pos += 20;

        $checkIntegrity = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$checkIntegrity (must be equal to $fileChangeCounter) : '.$checkIntegrity.'<br/>';

        $sqliteVersion = $this->getInt4d($this->pos);
        $this->pos += 4;
        echo '$sqliteVersion (must be equal to $fileChangeCounter) : '.$sqliteVersion.'<br/>';
    }

    public function getInt2d($pos)
    {
        $data = unpack('n',  substr($this->data, $pos, 2));
        return reset($data);
    }

    public function getInt4d($pos)
    {
        $data = unpack('N',  substr($this->data, $pos, 4));
        return reset($data);
    }
}