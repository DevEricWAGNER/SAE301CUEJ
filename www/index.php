<?php
session_start();

include('include/connexion.php');
$pdo = connexion();

	include('include/twig.php');
	$twig = init_twig();

	// Récupère les données GET sur l'URL
	if (isset($_GET['id'])) $id = $_GET['id']; else $id = 0;
	if (isset($_GET['page_id'])) $page_id = $_GET['page_id']; else $page_id = 0;
	if (isset($_GET['article_id'])) $article_id = $_GET['article_id']; else $article_id = 0;
	if (isset($_GET['element_id'])) $element_id = $_GET['element_id']; else $element_id = 0;
	if (isset($_GET['balise'])) $balise = $_GET['balise']; else $balise = '';
	if (isset($_GET['liaison'])) $liaison = $_GET['liaison']; else $liaison = '';
	if (isset($_GET['action'])) $action = $_GET['action']; else $action = '';
	if (isset($_GET['media_balise'])) $media_balise = $_GET['media_balise']; else $media_balise = '';
	if (isset($_GET['element1'])) $element1 = $_GET['element1']; else $element1 = "";
	if (isset($_GET['element2'])) $element2 = $_GET['element2']; else $element2 = "";


	// Convertit l'identifiant en entier
	$id = intval($id);
	$page_id = intval($page_id);
	$article_id = intval($article_id);
	$element_id = intval($element_id);

	// Connexion à la base de données
	include('include/page.php');
	include('include/article.php');
	include('include/element.php');
	


	function object_to_array($data) {
		if (is_array($data) || is_object($data)) {
			$result = [];
			foreach ($data as $key => $value) {
				$result[$key] = (is_array($value) || is_object($value)) ? object_to_array($value) : $value;
			}
			return $result;
		}
		return $data;
	}



	$count_pages = Page::readCountPage();
	$count_page = object_to_array($count_pages);

	$pages_readAll = Page::readAll();
	foreach($pages_readAll as $page) {
		$page->articles = Article::readByPage($page->id);
	}
	$articles_readAll = Article::readAll();
	$elements_readAll = Element::readAll();
	$unique_article = Article::readArticleHeader($article_id);
	$unique_page = Page::readPageHeader($page_id);
	$elements_Article_read = Element::readArticle($article_id);
	$articles_page_read = Article::readPage($page_id, $article_id);


	// $article_unique = object_to_array($unique_article);

	// if($count_page[0]['count_page'] <= 3) {
	// 	$nbr = (3-($count_page[0]['count_page']));
	// 	echo 'Vous pouvez encore créer '.$nbr.' Pages';
	// } else {
	// 	echo 'Vous ne pouvez plus créer de pages';
	// }
	
	$pdo = null;
	
	switch ($action) {

		// SITE ACCUEIL ET PAGES
		case 'site_accueil' :
			$view = 'site/read_accueil.twig';
			$data = [
				'accueil' => 'active',
				'articles' => $articles_readAll,
				'pages' => $pages_readAll,
				'contenu_page' => $articles_page_read,
				'unique_article' => $unique_article,
			];
		break;
		case 'site_article' :
			$view = 'site/read_article.twig';
			$data = [
                'unique_article' => $unique_article,
				'contenu_article' => $elements_Article_read,
				'articles' => $articles_readAll,
				'page_id' => $page_id,
				'pages' => $pages_readAll,
				
				'articles_in_unique_page' => $articles_page_read,
			];
		break;
		case 'site_credits' :
			$view = 'site/read_credits.twig';
			$data = [
				'accueil' => 'active',
				'articles' => $articles_readAll,
				'pages' => $pages_readAll,
				'contenu_page' => $articles_page_read,
			];
		break;

			default:
				$view = 'site/read_accueil.twig';
				$data = [
					'accueil' => 'active',
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'contenu_page' => $articles_page_read,
					'unique_article' => $unique_article,
				];
				break;
		}

	echo $twig->render($view, $data);

?>