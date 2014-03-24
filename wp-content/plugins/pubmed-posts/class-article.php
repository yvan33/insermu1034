<?php
/**
 * PubMed article class for "PubMed Posts" Wordpress plugin
 * http://www.nlm.nih.gov/bsd/licensee/elements_descriptions.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

class PubMedPostsArticle {

	const PUBMED_URL = 'http://www.ncbi.nlm.nih.gov/pubmed/';
	const PUBMED_QUERY = '?report=xml&format=text';

	/**
	 * PubMed ID
	 * @var integer
	 */
	private $pmid;
	
	/**
	 * SimpleXMLElement
	 * @var object
	 */	
	private $xml;

	/**
	 * Constructor
	 */
	function __construct($pmid = null) {
		if ($pmid) {
			$this->pmid = $pmid;
      $xml = $this->getXML($pmid);
			if (empty($xml)) {
				throw new Exception();
			} else {
				$this->xml = $xml;
			}
    }
	}
	
	/**
	 * Get article SimpleXMLElement
	 * @return object
	 */	
	public function getArticle() {	
		return $this->xml->MedlineCitation->Article;
	}
	
	/**
	 * Get article abstract
	 * @return object
	 */	
	public function getArticleAbstract() {	
		return $this->xml->MedlineCitation->Article->{'Abstract'}->AbstractText;
	}	

	/**
	 * Get article affiliation
	 * @return string
	 */	
	public function getArticleAffiliation() {	
		return (string) $this->xml->MedlineCitation->Article->Affiliation;
	}	
	
	/**
	 * Get article authors
	 * @return string
	 */	
	public function getArticleAuthors() {	
		$authors = $this->xml->MedlineCitation->Article->AuthorList;
		foreach ($authors->children() as $author) {
			$names[] = (string) $author->LastName . ' ' . (string) $author->Initials;
		}
		return implode(', ', $names);	
	}
	
	/**
	 * Get article date in ISO format
	 * @return string
	 */	
	public function getArticleDate() {	
		$year = $this->getArticleDateYear();
		$month = $this->getArticleDateMonth();
		$day = $this->getArticleDateDay();
		if (empty($year) || empty($month) || empty($day)) {
			return '';
		} else {
			return $year . '-' . $month . '-' . $day;
		}
	}	

	/**
	 * Get day from article date
	 * @return string
	 */	
	public function getArticleDateDay() {	
		return (string) $this->xml->MedlineCitation->Article->ArticleDate->Day;
	}		
	
	/**
	 * Get month from article date
	 * @return string
	 */	
	public function getArticleDateMonth() {	
		return (string) $this->xml->MedlineCitation->Article->ArticleDate->Month;
	}	
	
	/**
	 * Get year from article date
	 * @return string
	 */	
	public function getArticleDateYear() {	
		return (string) $this->xml->MedlineCitation->Article->ArticleDate->Year;
	}	

	/**
	 * Get article pagination
	 * @return string
	 */	
	public function getArticlePagination() {	
		return (string) $this->xml->MedlineCitation->Article->Pagination->MedlinePgn;
	}		
	
	/**
	 * Get article PMID
	 * @return string
	 */	
	public function getArticlePMID() {	
		return (string) $this->xml->MedlineCitation->PMID;
	}		
	
	/**
	 * Get article title
	 * @return string
	 */	
	public function getArticleTitle() {	
		$title = (string) $this->xml->MedlineCitation->Article->ArticleTitle;
		return rtrim($title, '.');
	}	
	
	/**
	 * Get article URL
	 * @return string
	 */	
	public function getArticleURL() {	
		return (string) self::PUBMED_URL . $this->pmid;
	}
	
	/**
	 * Build array of article data
	 * @return string
	 */	
	public function getData() {	
		$data = array();
		$data['article_abstract'] = $this->getArticleAbstract();		
		$data['article_affiliation'] = $this->getArticleAffiliation();
		$data['article_authors'] = $this->getArticleAuthors();
		$data['article_date'] = $this->getArticleDate();
		$data['article_pagination'] = $this->getArticlePagination();		
		$data['article_title'] = $this->getArticleTitle();		
		$data['article_url'] = $this->getArticleURL();
		$data['date_created'] = $this->getDateCreated();
		$data['date_completed'] = $this->getDateCompleted();
		$data['date_revised'] = $this->getDateRevised();				
		$data['journal_abbreviation'] = $this->getJournalAbbreviation();
		$data['journal_citation'] = $this->getJournalCitation();
		$data['journal_day'] = $this->getJournalDay();
		$data['journal_date'] = $this->getJournalDate();	
		$data['journal_issue'] = $this->getJournalIssue();
		$data['journal_month'] = $this->getJournalMonth();
		$data['journal_title'] = $this->getJournalTitle();
		$data['journal_volume'] = $this->getJournalVolume();
		$data['journal_year'] = $this->getJournalYear();
		$data['pmid'] = $this->pmid;
		return $data;
	}
	
	/**
	 * Get date created in ISO format
	 * @return string
	 */	
	public function getDateCreated() {	
		$year = $this->getDateCreatedYear();
		$month = $this->getDateCreatedMonth();
		$day = $this->getDateCreatedDay();
		if (empty($year) || empty($month) || empty($day)) {
			return '';
		} else {		
			$time = strtotime($year . '-' . $month . '-' . $day);
			return date('Y-m-d', $time);
		}
	}

	/**
	 * Get day from date created
	 * @return string
	 */	
	public function getDateCreatedDay() {	
		return (string) $this->xml->MedlineCitation->DateCreated->Day;
	}		
	
	/**
	 * Get month from date created
	 * @return string
	 */	
	public function getDateCreatedMonth() {	
		return (string) $this->xml->MedlineCitation->DateCreated->Month;
	}	
	
	/**
	 * Get year from date created
	 * @return string
	 */	
	public function getDateCreatedYear() {	
		return (string) $this->xml->MedlineCitation->DateCreated->Year;
	}
	
	/**
	 * Get date completed in ISO format
	 * @return string
	 */	
	public function getDateCompleted() {	
		$year = $this->getDateCompletedYear();
		$month = $this->getDateCompletedMonth();
		$day = $this->getDateCompletedDay();
		if (empty($year) || empty($month) || empty($day)) {
			return '';
		} else {
			$time = strtotime($year . '-' . $month . '-' . $day);
			return date('Y-m-d', $time);
		}
	}	

	/**
	 * Get day  from date completed
	 * @return string
	 */	
	public function getDateCompletedDay() {	
		return (string) $this->xml->MedlineCitation->DateCompleted->Day;
	}	
	
	/**
	 * Get month from date completed
	 * @return string
	 */	
	public function getDateCompletedMonth() {	
		return (string) $this->xml->MedlineCitation->DateCompleted->Month;
	}	
	
	/**
	 * Get year from date completed 
	 * @return string
	 */	
	public function getDateCompletedYear() {	
		return (string) $this->xml->MedlineCitation->DateCompleted->Year;
	}
	
	/**
	 * Get date revised in ISO format
	 * @return string
	 */	
	public function getDateRevised() {	
		$year = $this->getDateRevisedYear();
		$month = $this->getDateRevisedMonth();
		$day = $this->getDateRevisedDay();
		if (empty($year) || empty($month) || empty($day)) {
			return '';
		} else {		
			$time = strtotime($year . '-' . $month . '-' . $day);
			return date('Y-m-d', $time);
		}
	}	

	/**
	 * Get day from date revised
	 * @return string
	 */	
	public function getDateRevisedDay() {	
		return (string) $this->xml->MedlineCitation->DateRevised->Day;
	}		
	
	/**
	 * Get month from date revised
	 * @return string
	 */	
	public function getDateRevisedMonth() {	
		return (string) $this->xml->MedlineCitation->DateRevised->Month;
	}	
	
	/**
	 * Get year from date revised
	 * @return string
	 */	
	public function getDateRevisedYear() {	
		return (string) $this->xml->MedlineCitation->DateRevised->Year;
	}			
	
	/**
	 * Get journal title ISO abbreviation
	 * @return string
	 */	
	public function getJournalAbbreviation() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->ISOAbbreviation;
	}
	
	/**
	 * Get journal citation
	 * http://www.nlm.nih.gov/bsd/policy/cit_format.html
	 * @return string
	 */	
	public function getJournalCitation() {		
		// Get data
		$citation = $this->getJournalAbbreviation();
		$year = $this->getJournalYear();
		$month = $this->getJournalMonth();
		$medline = $this->getJournalMedlineDate();
		$volume = $this->getJournalVolume();
		$issue = $this->getJournalIssue();
		$pagination = $this->getArticlePagination();
		// Build citation
		if (!empty($year)) {
			$citation .= ' ' . $year;
			if (!empty($month)) {
				$citation .= ' ' . $month;
			}
		} else {
			if (!empty($medline)) {
				$citation .= ' ' . $medline;
			}
		}
		$citation .= ';' . $volume;
		if (!empty($issue)) {
			$citation .= '(' . $issue . ')';
		}		
		if (!empty($pagination)) {
			$citation .= ':' . $pagination;
		}
		return $citation;
	}
	
	/**
	 * Get journal publish date in ISO format
	 * @return string
	 */	
	public function getJournalDate() {	
		$date = '';
		$year = $this->getJournalYear();
		$month = $this->getJournalMonth();
		$day = $this->getJournalDay();			
		if (empty($year)) {
			// Try to parse MEDLINE date
			$medline_date = $this->getJournalMedlineDate();
			if (!empty($medline_date)) {
				$components = $this->getMedlineDateComponents($medline_date);
				$year = empty($components['year']) ? '' : $components['year'];
				$month = empty($components['month']) ? '' : $components['month'];
				$day = empty($components['day']) ? '' : $components['day'];
			}
		}
		if (!empty($year)) {
			$month = empty($month) ? '1' : $month;
			$day = empty($day) ? '1' : $day;		
			$time = strtotime($year . '-' . $month . '-' . $day);
			if ($time) {
				$date = date('Y-m-d', $time);
			}		
		}
		return $date;
	}		
	
	/**
	 * Get journal day
	 * @return string
	 */	
	public function getJournalDay() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->PubDate->Day;
	}	

	/**
	 * Get journal ISSN
	 * @return string
	 */	
	public function getJournalISSN() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->ISSN;
	}	

	/**
	 * Get journal issue
	 * @return string
	 */	
	public function getJournalIssue() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->Issue;
	}
	
	/**
	 * Get journal MEDLINE date
	 * @return string
	 */	
	public function getJournalMedlineDate() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->PubDate->MedlineDate;
	}		

	/**
	 * Get journal month
	 * @return string
	 */	
	public function getJournalMonth() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->PubDate->Month;
	}	

	/**
	 * Get journal title
	 * @return string
	 */	
	public function getJournalTitle() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->Title;
	}	
	
	/**
	 * Get journal volume
	 * @return string
	 */	
	public function getJournalVolume() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->Volume;
	}	
	
	/**
	 * Get journal year
	 * @return string
	 */	
	public function getJournalYear() {	
		return (string) $this->xml->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year;
	}	
	
	/** 
	 * Get MEDLINE date components
	 *
	 * MEDLINE is compiled by the United States National Library of Medicine.
	 * It is assumed that seasons are for the northern hemishpere.
	 * Only gets the first date of a range. Typos are converted as follows:
	 * "2rd Semest" to "2nd Semest"
	 * "4th Trimest" to "4th Quart" 	
	 *
	 * @param string $date
	 * @return string
	 */
	public function getMedlineDateComponents($date) {
		$year = $month = $day = '';
		$ordinals = array('1st', '2nd', '2rd', '2d', '3rd', '3rd', '3d', '4th');		
		$pattern = '/^(\d{4})([ \-])?([A-Za-z0-9]+)?([ \-])?([A-Za-z0-9]+)?([ \-])?([A-Za-z0-9]+)?([ \-])?([A-Za-z0-9]+)?([ \-])?([A-Za-z0-9]+)?([ \-])?/'; 
		preg_match($pattern, $date, $matches);
		// Get year
		$year = empty($matches[1]) ? '' : $matches[1];
		// Get month and day
		$separator = empty($matches[2]) ? '' : $matches[2];
		if (' ' == $separator) {
			$str1 = empty($matches[3]) ? '' : $matches[3];
			$separator = empty($matches[4]) ? '' : $matches[4];
			// Get academic term or day
			if ($str1 && ' ' == $separator) {
				$str2 = empty($matches[5]) ? '' : $matches[5];
				if ($str2) {
					if (in_array($str1, $ordinals)) {
						$str1 .= ' ' . $str2;
					} else {
						$day = $str2;
					}
				}
			}
			$month = $this->getMedlineDateMonth($str1);
		}
		// Output date
		$month = empty($month) ? '1' : $month;
		$day = empty($day) ? '1' : $day;			
		$time = strtotime($year . '-' . $month . '-' . $day);
		if ($time) {
			return array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
			);
		} else {
			return array();
		}
	}
	
	/**
	 * Get month from Medline date
	 * @param $str string
	 * @result string
	 */
	public function getMedlineDateMonth($str) {
		$str = strtolower($str);
		switch ($str) {
			case '1st semester' :
			case '1st semest' :
			case '1st trimest' :
			case '1st quart' :
				return '01';
			
			case 'spring' :
				return '03';
			
			case '2nd quart' :
			case '2d quart' :
				return '04';
			
			case '2nd trimest' :
			case '2d trimest' :
				return '05';
			
			case 'summer' :
				return '06';
			
			case '2nd semester' :
			case '2d semester' :
			case '2nd semest' :
			case '2rd semest' : // typo found
			case '2d semest' :
			case '3rd quart' :
			case '3d quart' :
				return '07';
			
			case 'autumn' :
			case '3rd trimest' :
			case '3d trimest' :
				return '09';
			
			case '4th quart' :
			case '4th trimest' : // typo found
				return '10';
			
			case 'winter' :
			case 'christmas' :
				return '12';
			
			default :
				$date = date_parse($str);
				return (string) $date['month']; 
		}
	}	
	
	/**
	 * Get XML for PubMed ID
	 */	
	public function getXML($pmid) {
		// Get HTML
		$url = self::PUBMED_URL . $pmid . self::PUBMED_QUERY;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		$html = curl_exec($ch); 
		curl_close($ch);
		if (empty($html)) {
			return false;
		}	
		// Get XML
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		foreach ($dom->getElementsByTagName('pre') as $node) {
			$xml = @simplexml_load_string($node->nodeValue); // suppress errors
		}
		return $xml;
	}		

}

// END