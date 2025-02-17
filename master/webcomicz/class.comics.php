<?PHP
class comics extends webcomicz_DB_Object {

	public $primarykey = 'comic';
	
	public function syncTags($f_comic=false) {
		$t_ctag = new comictags();
		if($f_comic == false) {
			$comics = $t_ctag->getTaggedComicList();
			foreach ($comics as $comic) {
				$tags = $t_ctag->getSingleList('tag','tag',array('comic'=>$comic['comic']));
				$this->update(array('com_tags'=>implode(',',$tags)),array('comic'=>$comic['comic']));
			}
			return $this->updateTextSearch();
		} else {
			if(!is_numeric($f_comic) || !$this->load(array('comic'=>$f_comic))) { return false;}
			$tags = $t_ctag->getSingleList('tag','tag',array('comic'=>$f_comic));
			if(!$this->update(array('com_tags'=>implode(',',$tags)),array('comic'=>$f_comic))) { return false;}
			return $this->updateTextSearch($f_comic);
		}
	}
	
	public function updateTextSearch ($f_comic = false) {
		if($f_comic == false) {
			$query = "UPDATE comics SET com_tsvector = setweight(to_tsvector(coalesce(title,'')), 'A') || setweight(to_tsvector(coalesce(synopsis,'')), 'C') || setweight(to_tsvector(coalesce(com_tags,'')), 'B') || setweight(to_tsvector(coalesce(url,'')), 'D');";
			return @pg_query($query);
		} else {
			if(!$this->load($f_comic)) { return false;}
			$query = "UPDATE comics SET com_tsvector = setweight(to_tsvector(coalesce(title,'')), 'A') || setweight(to_tsvector(coalesce(synopsis,'')), 'C') || setweight(to_tsvector(coalesce(com_tags,'')), 'B') || setweight(to_tsvector(coalesce(url,'')), 'D') ";
			$query .= " WHERE comic = " .  $f_comic . ";";
			return @pg_query($query);
		}	
	}
	
	
	public function getAnniversaries() {
		$q= "SELECT comic,title,started, age(started), 
		age((extract('year' from NOW())||'-'||extract('month' from started)||'-'||extract('day' from started))::DATE) as anniversary,
		EXTRACT('year' from AGE((extract('year' from NOW())||'-'||extract('month' from started)||'-'||extract('day' from started))::DATE, started)) as age 
		FROM comics WHERE started IS NOT NULL AND  
		age((extract('year' from NOW())||'-'||extract('month' from started)||'-'||extract('day' from started))::DATE) BETWEEN '-7 days'::INTERVAL AND '-0 DAYS'::INTERVAL;";
		
		return @pg_fetch_assoc(@pg_query($q));
	}
	
	public function checkRSSUpdates ($todayOnly = true) {
		$t_rss = new lastRSS;
		$t_rss->CDATA = 'content';
		$t_rss->items_limit = 1;
		$t_rss->itemtags[] = 'pubDate';
		
		$rssFeeds = $this->getRSS($todayOnly);
		foreach ($rssFeeds as $feed) {
                    echo $feed['title'];
			$r = $t_rss->get($feed['rss']);
			if(!empty($r['items'][0]['pubDate']) || !empty($r['lastBuildDate'])) {
				if(!empty($r['items'][0]['pubDate'])) {
					$update['pubDate'] = date("Y-m-d H:i:s",strtotime($r['items'][0]['pubDate']));
				} else {
					$update['pubDate'] = date("Y-m-d H:i:s",strtotime($r['lastBuildDate']));
				}
				$update['description'] = $r['items'][0]['description'];
				$update['link'] = $r['items'][0]['link'];
				$this->setUpdated($feed['comic'], $update);
			} else {
				$pubDate = false;
			}
		}
                return false;
	}

	public function setUpdated($f_comic, $fa_update) {
		if(empty($f_comic) || !is_numeric($f_comic)) { return false;}
		$update['com_update'] = $fa_update['pubDate'];
		if(!empty($fa_update['description'])) {$update['com_update_description'] = trim(strip_tags(htmlspecialchars_decode(htmlspecialchars($fa_update['description']))));}
		if(!empty($fa_update['link'])) {$update['com_update_link'] = trim(strip_tags($fa_update['link']));}

		return $this->update($update,array('comic'=>$f_comic));

	}
	public function getRSS ($todayOnly=true) {
		$query =($todayOnly) ?   "SELECT comic, title, rss from comics WHERE rss IS NOT NULL AND rss <> '' AND (com_update IS NULL OR date(com_update) <> CURRENT_DATE) AND " . strtolower(date('l')) . "  = true;" : "SELECT comic, title, rss from comics WHERE rss IS NOT NULL AND rss <> '' AND (com_update IS NULL OR date(com_update) < CURRENT_DATE);";
                
		return @pg_fetch_all(@pg_query($query));	
	}
	
	public function setRank ($f_comic,$f_rank=false) {
                $rank = ($f_rank) ? $f_rank : '(SELECT max(rank) +1 FROM comics)';

		$query = 'UPDATE comics set rank = ' . $rank . ' where comic =' .$f_comic .';';
		return @pg_query($query);
	}


        public function createRankImage($f_comic,$f_rank) {

        }

	public function checkExitFraud() {
		
		$query = "update exits set exit_fraud = true where exit_referer !~* 'http://www.webcomicz.com' and exit_fraud = false;";
		return @pg_query($query);
	}
	
	public function calculateRank () {
            
            
            
            return false;



		$comics = $this->getRank2();
		$query = '';
		$this->checkExitFraud();
		foreach ($comics as $k=>$v) {
			//$this->setRank($v['comic'],($k+1));
			$query .= 'UPDATE comics set rank = ' . ($k+1) . ' where comic =' . $v['comic'] .'; ';
		}
		return @pg_query($query);
	}
	
	
	public function getRank2 () {
	$percent['exits'] = .0; 			// Number of Total Exits
	$percent['unique_exits'] = .05;		// .05 Number of Unique Exits
	$percent['member'] = 2; 			// 1.5 Member Votes
	$percent['nonmember'] = .7; 		// .70 Non-member Votes
	$percent['favorites'] = 1.25; 		// 1.25 Members Favorited
	$percent['tags'] = .02; 			// .02 Tags Assigned to Webcomic
	$query = "SELECT comic, title,
		((SELECT count(exit) from exits where exits.comic = comics.comic AND exits.stamp >= NOW() - INTERVAL '1 MONTH' and exit_fraud = false) * " . $percent['exits'] . ") as exits,
		((SELECT count(DISTINCT ip) from exits where exits.comic = comics.comic AND exits.stamp >= NOW() - INTERVAL '1 MONTH' and exit_fraud = false) * " . $percent['unique_exits'] . ") as unique_exits,
		((SELECT count(vote) FROM votes WHERE votes.comic = comics.comic AND votes.day >= CURRENT_DATE - INTERVAL '1 month' AND member is NOT NULL and vote_fraud = false) * " . $percent['member'] . " ) as members_votes,
		((SELECT count(vote) FROM votes WHERE votes.comic = comics.comic AND votes.day >= CURRENT_DATE - INTERVAL '1 month' AND member is NULL and vote_fraud = false) * " . $percent['nonmember'] . ") as nonmember_votes,
		((SELECT count(marked) FROM favorites LEFT JOIN members ON (favorites.member = members.member) WHERE favorites.comic = comics.comic AND suspend = false) * " . $percent['favorites'] . ") as favorites,
		((SELECT count(DISTINCT tagger) FROM comictags WHERE comictags.comic = comics.comic) * " . $percent['tags'] . ") as tags,";
	
	
	$query .= " 
		((SELECT count(exit) from exits where exits.comic = comics.comic AND exits.stamp >= NOW() - INTERVAL '1 MONTH' and exit_fraud = false) * " . $percent['exits'] . ") + 
		((SELECT count(DISTINCT ip) from exits where exits.comic = comics.comic AND exits.stamp >= NOW() - INTERVAL '1 MONTH' and exit_fraud = false) * " . $percent['unique_exits'] . ") + 
		((SELECT count(vote) FROM votes WHERE votes.comic = comics.comic AND votes.day >= CURRENT_DATE - INTERVAL '1 month' AND member is NOT NULL and vote_fraud = false) * " . $percent['member'] . " ) +
		((SELECT count(vote) FROM votes WHERE votes.comic = comics.comic AND votes.day >= CURRENT_DATE - INTERVAL '1 month' AND member is NULL and vote_fraud = false) * " . $percent['nonmember'] . ") +
		((SELECT count(marked) FROM favorites LEFT JOIN members ON (favorites.member = members.member) WHERE favorites.comic = comics.comic AND suspend = false) * " . $percent['favorites'] . ") + 
		((SELECT count(DISTINCT tagger) FROM comictags WHERE comictags.comic = comics.comic) * " . $percent['tags'] . ") as score
		
		FROM comics ORDER BY score desc ;";
		return @pg_fetch_all(@pg_query($query . ';'));
		
		
	}
	
	public function getRank ($range=false,$limit=10,$group='day') {
		$query = "SELECT title, 
                    comics.comic,
                    added,
                    count(vote),
                    imgfile as count
                    
                    FROM comics
                    LEFT JOIN votes USING (comics.comic)
                    LEFT JOIN favorites USING (comics.comic)
                    LEFT JOIN exits ON (exits.comic = comics.comic AND exits.stamp >= NOW() - INTERVAL '1 month' ) ";
		if (!empty($range)) {
			if ($range === true) {
				$start = date('Y-m-01');
				$stop = date('Y-m-t');
			} else {
				$start = $range['start'];
				$stop = $range['stop'];
			}
			$query .= " WHERE day between '$start' AND '$stop' ";
		}
		$query .= " group by title, comics.comic, added, imgfile ORDER BY (count(vote) + count(marked)) desc, added asc ";
		if ($limit) {  $query .= " LIMIT $limit ";}
		return @pg_fetch_all(@pg_query($query . ';'));
	}
	
	public function getNewComics ($limit=4) {
		$query = 'SELECT * from comics WHERE imgfile IS NOT NULL ORDER BY comic desc ';
		if ($limit) { $query .= 'LIMIT ' . $limit ; }
		return @pg_fetch_all(@pg_query ($query . ';'));
	
	}
	
	public function applyIcon ($icon) {
		return $this->update($icon,array('comic'=>$icon['comic']));		
	}
	
	public function applyEdit ($edits) {
		$cg = new comicgenres();
		$cs = new comicstyles();
		$cf = new comicformats();
		for ($a=1; $a<=3; $a++) {
			if (!empty($edits['genre'.$a])) {$genres[$a] = array('comic'=>$edits['comic'],'genre'=>$edits['genre'.$a]);}
			if (!empty($edits['style'.$a])) {$styles[$a] = array('comic'=>$edits['comic'],'style'=>$edits['style'.$a]);}
			if (!empty($edits['format'.$a])) {$formats[$a] = array('comic'=>$edits['comic'],'format'=>$edits['format'.$a]);}
		}
		
		$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
		foreach ($days as $day) {
			if (empty($edits[$day])) { $edits[$day] = false;}
		}
		if (empty($edits['mature'])) { $edits['mature'] = false;}
		if ($this->update($edits,array('comic'=>$edits['comic']))) {
			if (!$cg->replace($genres,$edits['comic'])) { return false;}
			if (!$cs->replace($styles,$edits['comic'])) { return false;}
			if (!$cf->replace($formats,$edits['comic'])) { return false;}
		}
		return $this->updateTextSearch();
	}
	
	public function applySuggest ($suggets) {
		$cg = new comicgenres();
		$cs = new comicstyles();
		$cf = new comicformats();
		for ($a=1; $a<=3; $a++) {
			if (!empty($suggets['genre'.$a])) {$genres[$a] = array('comic'=>$suggets['comic'],'genre'=>$suggets['genre'.$a]);}
			if (!empty($suggets['style'.$a])) {$styles[$a] = array('comic'=>$suggets['comic'],'style'=>$suggets['style'.$a]);}
			if (!empty($suggets['format'.$a])) {$formats[$a] = array('comic'=>$suggets['comic'],'format'=>$suggets['format'.$a]);}
		}
		
		$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
		foreach ($days as $day) {
			if (empty($suggets[$day])) { $suggets[$day] = false;}
		}
		if (empty($suggets['mature'])) { $suggets['mature'] = false;}
		if ($this->add($suggets)) {
			if (!$cg->replace($genres,$suggets['comic'])) { return false;}
			if (!$cs->replace($styles,$suggets['comic'])) { return false;}
			if (!$cf->replace($formats,$suggets['comic'])) { return false;}
		}
		return true;
	}
	
	
	public function update($values,$where=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$t_values = $this->cleanValues($this->pruneValues($values));
		$query = "UPDATE " . $table;
		$query .= $this->formatSet($t_values) . ", updated = NOW()"; //, com_tsvector = setweight(to_tsvector(coalesce(title,'')), 'A') || setweight(to_tsvector(coalesce(synopsis,'')), 'B') 
		if ($where) {$query .= $this->formatWhere($where);}
		return @pg_query($query . ';');
	}
	

	public function insert($values, $table = false) {
		if (!$table) {$table = $this->table;}
		$t_values = $this->pruneValues($values);
		if (!empty($t_values)) {
			$query = "INSERT INTO " . $table . " (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',',$this->cleanValues($t_values)) . ")";
                        @pg_query($query . ';');
			$re = @pg_fetch_assoc(@pg_query("select currval('comics_comic_seq');"));
			return $re['currval'];
		}
		return false;
	}

        
	public function search ($term) {
		return $this->getList('title', " title ~* '". $term ."' ");
	}
	
	public function leave ($id,$from='profile') {
		$ex = new exits();
		$assoc = $this->load($id);
		$exit['comic'] = $id;
		$exit['exit'] = $ex->getNext('exit',array('comic'=>$id));
		$exit['ip'] = $_SERVER['REMOTE_ADDR'];
		$exit['exit_from'] = $from;
		$exit['exit_referer'] = $_SERVER['HTTP_REFERER'];
		$exit['exit_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$ex->add($exit);
		header ("location: " . $assoc['url']);
	}
	
	public function load ($where,$order=false,$table = false) {
		$t_genres = new comicgenres();
		$t_styles = new comicstyles();
		$t_formats = new comicformats();
		
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT *, 
		(SELECT count(vote) FROM votes WHERE votes.comic = comics.comic AND AGE(day) <= INTERVAL \'1 Month\'  and vote_fraud = false) as votes,
		(SELECT count(tag) FROM comictags WHERE comictags.comic = comics.comic) as tags,
		(SELECT count(comment) FROM comments where type=\'comics\' AND comments.id = comics.comic) as comments, 
		(SELECT count(exit) FROM exits WHERE exits.comic = comics.comic AND AGE(exits.stamp) <= INTERVAL \'1 Month\'  and exit_fraud = false) as exits,
		(SELECT count(DISTINCT exit) FROM exits WHERE exits.comic = comics.comic AND AGE(exits.stamp) <= INTERVAL \'1 Month\'  and exit_fraud = false) as unique_exits,
		(SELECT count(favorites.member) FROM favorites left join members ON (favorites.member = members.member) where favorites.comic = comics.comic AND suspend = false) as favorites, 
		(SELECT count(avatar) FROM avatars where avatars.source = comics.comic) as avatars,		
		case when mature then 1 else 0 end as mature,
		case when monday then 1 else 0 end as monday,	
		case when tuesday then 1 else 0 end as tuesday,	
		case when wednesday then 1 else 0 end as wednesday,	
		case when thursday then 1 else 0 end as thursday,	
		case when friday then 1 else 0 end as friday,	
		case when saturday then 1 else 0 end as saturday,	
		case when sunday then 1 else 0 end as sunday,
		(SELECT count(crossover) FROM crossovers WHERE origin = comic) as co_origin,
		(SELECT count(crossover) FROM crossovers WHERE visitor = comic) as co_visitor,
		(SELECT count(DISTINCT ac_article) FROM article_comics WHERE ac_comic = comic) as articles
		
		FROM ' . $table . ' LEFT JOIN members ON (member = comic_submitter) LEFT JOIN updates USING (update) LEFT JOIN (reviews  JOIN articles  ON (reviews.rev_article = articles.article AND art_show <= NOW() )) ON (comics.com_review = reviews.review) LEFT JOIN scores ON (rev_score = score) ';
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		if($t_arr = @pg_fetch_assoc(@pg_query($query . ';'))) {
			$t_arr['genres'] = $t_genres->getGenresByComic($t_arr['comic']);
			$t_arr['styles'] = $t_styles->getStylesByComic($t_arr['comic']);
			$t_arr['formats'] = $t_formats->getFormatsByComic($t_arr['comic']);
			$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
			foreach ($days as $day) {
				if ($t_arr[$day] == 't' || $t_arr[$day] == true) {
					$t_arr['days'][$day]['updates'] = true;
					$t_arr['days'][$day]['value'] = 1;
					$t_arr['days'][$day]['class'] = 'dotw_updates';
					$t_arr['days'][$day]['small'] = substr($day,0,3);
					$t_arr['days'][$day]['letter'] = substr($day,0,1);
				} else {
					$t_arr['days'][$day]['small'] = substr($day,0,3);
					$t_arr['days'][$day]['letter'] = substr($day,0,1);
					$t_arr['days'][$day]['updates'] = false;
					$t_arr['days'][$day]['value'] = 0;
				}
			}
		}
		return $t_arr;
	}
	
	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		$t_genres = new comicgenres();
		$t_styles = new comicstyles();
		$t_formats = new comicformats();
		
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *,
		case when ((NOW() - added) < INTERVAL \'1 week\') then 1 else 0 end as new, 
		extract(months from age(added)) as age, 
		case when mature then 1 else 0 end as mature,
		case when monday then 1 else 0 end as monday,	
		case when tuesday then 1 else 0 end as tuesday,	
		case when wednesday then 1 else 0 end as wednesday,	
		case when thursday then 1 else 0 end as thursday,	
		case when friday then 1 else 0 end as friday,	
		case when saturday then 1 else 0 end as saturday,	
		case when sunday then 1 else 0 end as sunday
		
		FROM ' . $table  . ' LEFT JOIN members ON (member = comic_submitter) LEFT JOIN updates USING (update) LEFT JOIN (reviews  JOIN articles  ON (reviews.rev_article = articles.article AND art_show <= NOW() )) ON (comics.com_review = reviews.review) LEFT JOIN scores ON (rev_score = score) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$re = @pg_query ($query . ';');
		while ($t_arr = @pg_fetch_assoc($re)) {
			$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
			foreach ($days as $day) {
				if ($t_arr[$day] == 't' || $t_arr[$day] == true) {
					$t_arr['days'][$day]['updates'] = true;
					$t_arr['days'][$day]['value'] = 1;
					$t_arr['days'][$day]['class'] = 'dotw_updates';
				} else {
					$t_arr['days'][$day] = false;
				}
			}
			$t_arr['genres'] = $t_genres->getGenresByComic($t_arr['comic']);
			$t_arr['styles'] = $t_styles->getStylesByComic($t_arr['comic']);
			$t_arr['formats'] = $t_formats->getFormatsByComic($t_arr['comic']);
			$arr[] = $t_arr;
		}
		return $arr;
	}
	
	//SELECT *, ts_rank_cd(com_tsvector, query) AS ts_rank  FROM comics, plainto_tsquery('sex') query  WHERE query @@ com_tsvector  ORDER BY ts_rank DESC;
	public function getSearchList ($f_query,$limit=false,$offset=false,$table=false) {
		$f_query = $this->escapeData($f_query);
		$t_genres = new comicgenres();
		$t_styles = new comicstyles();
		$t_formats = new comicformats();
		
		if (!$table) {$table = $this->table;}
		$query = 'SELECT *,
		case when ((NOW() - added) < INTERVAL \'1 week\') then 1 else 0 end as new, 
		extract(months from age(added)) as age, 
		(SELECT count(comment) FROM comments where type=\'comics\' AND comments.id = comics.comic) as comments, 
		(SELECT count(member) FROM favorites where favorites.comic = comics.comic) as favorites,
		case when mature then 1 else 0 end as mature,
		case when monday then 1 else 0 end as monday,	
		case when tuesday then 1 else 0 end as tuesday,	
		case when wednesday then 1 else 0 end as wednesday,	
		case when thursday then 1 else 0 end as thursday,	
		case when friday then 1 else 0 end as friday,	
		case when saturday then 1 else 0 end as saturday,	
		case when sunday then 1 else 0 end as sunday,
		(SELECT count(crossover) FROM crossovers WHERE origin = comic) as co_origin,
		(SELECT count(crossover) FROM crossovers WHERE visitor = comic) as co_visitor,
		(SELECT count(DISTINCT ac_article) FROM article_comics WHERE ac_comic = comic) as articles, 
		ts_rank_cd(com_tsvector, query) AS ts_rank
		
		FROM ' . $table  . " LEFT JOIN updates USING (update)LEFT JOIN (reviews  JOIN articles  ON (reviews.rev_article = articles.article AND art_show <= NOW() )) ON (comics.com_review = reviews.review)
                    LEFT JOIN scores ON (rev_score = score), plainto_tsquery('" . $f_query . "') query ";
		$query .= " WHERE query @@ com_tsvector ";
		$query .= " ORDER BY ts_rank DESC, title ";
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$re = @pg_query ($query . ';');
		while ($t_arr = @pg_fetch_assoc($re)) {
			$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
			foreach ($days as $day) {
				if ($t_arr[$day] == 't' || $t_arr[$day] == true) { 
					$t_arr['days'][$day] = true; 
				} else {
					$t_arr['days'][$day] = false;
				}
			}
			$t_arr['genres'] = $t_genres->getGenresByComic($t_arr['comic']);
			$t_arr['styles'] = $t_styles->getStylesByComic($t_arr['comic']);
			$t_arr['formats'] = $t_formats->getFormatsByComic($t_arr['comic']);
			$arr[] = $t_arr;
		}
		return $arr;
	}
	
	
	
	public function getCount ($column=false,$where=false,$group=false,$table=false) {
		if (!$table) {$table = $this->table;}
		if (empty($column) && !is_array($this->primarykey)) {$column = $this->primarykey;} 
		elseif (empty($column) && is_array($this->primarykey)) {$column = 0;}
		$query = 'SELECT count(' . $column . ') as count FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($group) {$query .= $this->formatGroup($group);}
		$ret = pg_fetch_assoc(pg_query($query . ';'));
		
		return $ret['count'];		
	}
	
}
?>