<?php
namespace lib\mvc\models;

trait terms
{
	/**
	 * return the detail of term
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_terms($_forcheck = false, $_url = null)
	{
		if(!$_url)
		{
			$_url = $this->url('path');
		}
		$qry = $this->sql()->tableTerms()->whereTerm_url($_url)->select();

		if($qry->num() == 1)
		{
			$datarow = $qry->assoc();

			if($_forcheck)
			{
				// set type of terms and remove prefix from it
				$mytype = $datarow['term_type'];
				if(substr($mytype, 0, 4) === 'cat_')
				{
					$mytype = substr($mytype, 4);
				}
				// set cat of this term and remove prefix
				$cat = $datarow['term_url'];
				if($mytype === substr($cat, 0, strlen($mytype)))
				{
					$cat = substr($cat, strlen($mytype)+1);
				}

				return
				[
					'table' => 'terms',
					'type' => $mytype,
					'cat'  => $cat,
					'slug' => $datarow['term_slug'],
				];
			}
			else
				return $datarow;
		}
		return false;
	}


	/**
	 * return list of cats in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_catsInTerm()
	{
		$url = $this->url('path');

		$qry_id = $this->sql()->table('terms')->where('term_url', $url)->select()->assoc('id');
		$datatable = $this->sql()->table('terms')->where('term_parent', $qry_id)->select()->allassoc();
		// var_dump($datatable);
		return $datatable;
	}


	/**
	 * return list of posts in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_postsInTerm($_limit = null)
	{
		$url = $this->url('path');
		if(substr($url, 0, 4) === 'tag/')
		{
			$url = substr($url, 4, (int) $url);
		}

		if(substr($url, 0, 11) === 'book-index/')
		{
			preg_match("#^book-index/([^\/]*)(.*)$#", $url, $m);
			$url_raw = "book/$m[1]";


			if($m[2] !== '')
			{
				$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'ASC');
				$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
				$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id');
			}
			else
			{
				$parent_id = $this->sql()->table('posts')->where('post_url', $url_raw)
					->and('post_status', 'publish')->select()->assoc('id');
				$qry = $this->sql()->table('posts')->where('post_parent', $parent_id)
					->and('post_status', 'publish')->order('id', 'ASC');
			}


			return $qry->select()->allassoc();
		}

		$qry = $this->sql()->table('posts')->where('post_status', 'publish')->order('id', 'DESC');
		$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"')->field(false);
		$qry->join('terms')->on('id', '#termusages.term_id')->and('term_url', $url)->groupby('#posts.id')->field(false);
		// hasan :|
		$pagenation = [];
		if($_limit){
			$qryCount = clone $qry;
			$qryCount->field("#count(posts.id)");
			$count = $qryCount->select()->num();
			$pagenationPages 	= ceil($count / $_limit);
			$pagenationCurrent 	=  \lib\router::get_storage("pagenation");
			$pagenationNext 	= \lib\router::get_storage("pagenation") +1;
			$pagenationPrev 	= \lib\router::get_storage("pagenation") -1;
			if($pagenationCurrent !== null
				AND ($pagenationCurrent < 1 || $pagenationCurrent > $pagenationPages)){
					\lib\error::page(T_("Does not exist!"));
					return;
			}
			$pagenation = [
			"num_page"		=> $pagenationPages,
			"pages" 		=> intval($pagenationPages),
			"current" 		=> ($pagenationCurrent == 0)? 1 : intval($pagenationCurrent),
			"next" 			=> ($pagenationNext <= $pagenationPages) ? $pagenationNext : false,
			"prev" 			=> ($pagenationPrev >= 1) ? $pagenationPrev : false,
			"count_link"	=> 7,
			"current_url" 	=> \lib\router::get_url(),
			];
			$start = (\lib\router::get_storage("pagenation")) ? (\lib\router::get_storage("pagenation") -1) * $_limit : 0;


			$qry->limit($start, $_limit);
		}
		return ["pagenation" => $pagenation, "result" => $qry->select()->allassoc()];
	}
}
?>
