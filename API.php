<?php

namespace lamanweb\bookcafe;

use lamanweb\bookcafe\Request;
/**
 * Class MauKerja
 * @package  MauKerja
 * @author   LamanWeb Solutions <lamanweb dot com>
 * @version 1.0.0 2019-05-22
 * @link https://github.com/lamanweb/testing
 */

class API {
    private static $instance = null;
    public $xml;

    public static function getBooks() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }

    public function curl_get($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.maukerja.my/ms');
        $dir = dirname(__FILE__);
        $config['cookie_file'] = $dir.
        '/cookies/'.md5($_SERVER['REMOTE_ADDR']).
        '.txt';
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $config['cookie_file']);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $config['cookie_file']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public
    function __construct() {

        $this->page = isset($_GET['page']) ? $_GET['page'] : 1;
        $query = Request::get('query');
        $this->query = $this->uc_words($query);
        $location = Request::get('loc');
        $this->location = $this->uc_words($location);
        $this->kategori = Request::get('cat');
        $this->type = Request::get('type');
        $this->jobid = Request::get('id');

        $this->pagestart = 0;
        $this->pageend = 10;
        if ($this->page > 1) {
            $this->pagestart = ($this->page - 1) * 10;
            $this->pageend = $start + 10;
        }

        $this->negeri = array("Johor", "Kelantan", "Kuala Lumpur", "Labuan", "Melaka", "Negeri Sembilan", "Pahang", "Perak", "Pulau Pinang", "Putrajaya", "Sabah", "Sarawak", "Selangor", "Terengganu", "Perlis", "Kedah");

        if (in_array($this->location, $this->negeri)) {
            $this->lokasi = "region";
        } else {
            $this->lokasi = "city";
        }

        $this->arraykategori = array('Call Center / BPO' => '1', 'Advertising / Marketing' => '2', 'Food & Beverage' => '3', 'Beauty / Fitness' => '4', 'Customer Service / Helpdesk' => '5', 'Retail / Merchandise' => '6', 'IT / Software' => '7', 'Admin / Clerical' => '8', 'Business / Mgmt Consulting' => '9', 'Engineering / Technical Consulting' => '10', 'IT / Hardware' => '11', 'Accounting / Tax Services' => '12', 'Manufacturing / Production' => '13', 'Arts / Design / Fashion' => '14', 'Sales / Biz Development' => '15', 'Heavy Industrial / Machinery' => '16', 'Exhibitions / Event Mgmt' => '17', 'Education / Training' => '18', 'HR Mgmt / Consulting' => '19', 'Entertainment / Media' => '20', 'Electrical & Electronics' => '21', 'Purchase / Supply Chain' => '22', 'Construction / Building' => '23', 'Environment / Health / Safety' => '24', 'Transportation / Logistics' => '25', 'Banking / Finance' => '26', 'Repair / Maintenance' => '27', 'Social Services / NGO' => '28', 'Automobile / Automotive' => '29', 'Insurance' => '30', 'Other industries' => '31', 'Agriculture / Poultry / Fisheries' => '32', 'Architecture / Interior Design' => '33', 'Travel / Tourism' => '34', 'Hotel / Hospitality' => '35', 'Property / Real Estate' => '36', 'Gems / Jewellery' => '37', 'Security / Law Enforcement' => '38', 'Apparel' => '39', 'Healthcare / Medical' => '40', 'R&D' => '41', 'Journalism' => '42', 'General & Wholesale Trading' => '43', 'Law / Legal' => '44', 'Telecommunication' => '45', 'Chemical / Fertilizers' => '46', 'Sports' => '47', 'BioTech / Pharmaceutical' => '48', 'Consumer Products / FMCG' => '49', 'Printing / Publishing' => '50', 'Medical / Healthcare / Beauty' => '51', 'Science & Technology' => '52');

        if (in_array($this->kategori, $this->arraykategori)) {
            $this->jobkategori = array_search($this->kategori, $this->arraykategori);
        }

        $this->arraytype = array('Full Time' => '1', 'Part Time' => '2', 'Full Time,Part Time' => '3', 'Internship' => '4');
        if (in_array($this->type, $this->arraytype)) {
            $this->jobtype = array_search($this->type, $this->arraytype);
        }

        libxml_use_internal_errors(true);
        $xmll = 'jobxml.xml';
        $oldxml = 'oldjob.xml';
        ob_flush();
        $this->xml = simplexml_load_file(utf8_encode($xmll));
        ob_clean();

        if ($this->xml === false) {
            ob_flush();
            $this->xml = simplexml_load_file(utf8_encode($oldxml));
            ob_clean();
        }

        if ($this->jobid AND!$this->query AND!$this->location AND!$this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[contains(id, '".$this->jobid.
                "')]");
        } else if (!$this->query AND!$this->location AND!$this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages,'Chinese')) and not(contains(company,'Pizza Hut Restaurants Sdn Bhd')) and not(contains(salary,'IDR'))]");
            $this->location = "Malaysia";
            $this->query = "Kerja Kosong";
        } else if ($this->query AND!$this->location AND!$this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages, 'Chinese')) and contains(title, '".$this->query.
                "') or contains(company, '".$this->query.
                "')]");
            $this->location = "Malaysia";
        } else if (!$this->query AND $this->location AND!$this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages, 'Chinese')) and contains(".$this->lokasi.
                ", '".$this->location.
                "')]");
            $this->query = "Kerja Kosong";
        } else if ($this->query AND $this->location AND!$this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages, 'Chinese')) and contains(title, '".$this->query.
                "') and contains(".$this->lokasi.
                ", '".$this->location.
                "')]");
        } else if (!$this->query AND!$this->location AND $this->kategori AND!$this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages, 'Chinese')) and contains(category, '".$this->jobkategori.
                "')]");
            $this->query = "Kerja Kosong";
        } else if (!$this->query AND!$this->location AND!$this->kategori AND $this->type) {
            $this->res = $this->xml->xpath("//adhance/job[not(contains(languages, 'Chinese')) and contract='".$this->jobtype.
                "']");
        }

        $this->num = count($this->res);
        $this->muka = ceil($this->num / 10);

        /** if xml no job

        if(count($this->res) == 0)
        {
        $this->startpage = $this->page - 1;
        $this->apimaukerja = 
        $this->api = "https://www.maukerja.my/api/job/listing?search_salary_currency=RM&sort_by=date&freshgraduate=true&fastresponse=false&hotjobs=false&urgentjobs=false&direct_contact=false&orderby=desc&start=".$this->startpage."&noOfRecords=10&pagename=joblisting&jobsearch_country_name=Malaysia&_token=&_method=GET";

        if($this->query AND !$this->location AND !$this->kategori AND !$this->type)
        {
        $this->query = str_replace(' ','+',$this->query);
        $this->res = $this->api."&keyword=".$this->query."";
        $this->res = $this->curl_get($this->res);
        $this->res = json_decode($this->res);
        }
        $this->countjob = 0;
        }

        */

    }

    public
    function __destruct() {}

    public
    function trim_all($word) {
        return trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($word))))));
    }

    public
    function canonical($word) {
        return strtolower(str_replace(" ", "-", $this->trim_all($word)));
    }

    public
    function trim_id($id) {
        $id = (string) $id;
        return substr($id, 1, -1);
    }

    function timePassed($time_ago) {
        $time_ago = strtotime($time_ago);
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;

        if ($time_elapsed < 1) {
            return '0 saat';
        }

        $a = array(365 * 24 * 60 * 60 => 'tahun',
            30 * 24 * 60 * 60 => 'bulan',
            24 * 60 * 60 => 'hari',
            60 * 60 => 'jam',
            60 => 'minit',
            1 => 'saat'
        );
        $a_plural = array('tahun' => 'tahun',
            'bulan' => 'bulan',
            'hari' => 'hari',
            'jam' => 'jam',
            'minit' => 'minit',
            'saat' => 'saat'
        );

        foreach($a as $secs => $str) {
            $d = $time_elapsed / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r.
                ' '.($r > 1 ? $a_plural[$str] : $str).
                ' lalu';
            }
        }
    }

}