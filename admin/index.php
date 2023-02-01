<?php
session_start();

include('include/connexion.php');
$pdo = connexion();

$getid = intval($_SESSION['id']);
$requser = $pdo->prepare('SELECT * FROM membres WHERE id = ?');
$requser->execute(array($getid));
$userinfo = $requser->fetch();

	if ($userinfo['admin'] == "1") {
		
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
	
			// ARTICLES
			case 'read_article' :
				$view = 'article/read_article.twig';
				$data = [
					'unique_article' => $unique_article,
					'contenu_article' => $elements_Article_read,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
					'articles_in_unique_page' => $articles_page_read,
				];
			break;
			case 'create_article' :
				$view = 'article/create_article.twig';
				$data = [
					'create_article' => 'active',
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'edit_article' :
				$view = 'article/edit_article.twig';
				$data = [
					'id_article' => $article_id,
					'articles' => $articles_readAll,
					'edit_article' => 'active',
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'edit_article_parametres' :
				$view = 'article/edit_article_parametres.twig';
				$data = [
					'id_article' => $article_id,
					'article_unique' => Article::readOne($article_id),
					'element_id' => $article_id,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'article_parametres' :
				header('Location: index.php?action=edit_article_parametres&article_id='.$_POST['article_to_edit']);
			break;
			case 'update_article' :
				$view = 'article/update_article.twig';
				$data = [
					'contenu_article' => $elements_Article_read,
					'edit_article' => 'active',
					'id_article' => $article_id,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'delete_article' :
				$view = 'article/delete_article.twig';
				$data = [
					'articles' => $articles_readAll,
					'id_article' => $article_id,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'read' :
				$view = 'article/read.twig';
				$data = [
					'articles' => $articles_readAll,
					'read' => 'active',
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'article_delete' :
				$article = new Article();
				$article->delete($_POST['article_id']);
				header('Location: index.php');
			break;
			case 'new_article' :
				if (isset($_POST['submit']) && isset($_FILES['article_img'])) {
					echo "<pre>";
					print_r($_FILES['article_img']);
					echo "</pre>";
					$img_name = $_FILES['article_img']['name'];
					$img_size = $_FILES['article_img']['size'];
					$tmp_name = $_FILES['article_img']['tmp_name'];
					$error = $_FILES['article_img']['error'];
	
					if ($error === 0) {
					  if ($img_size > 12500000) {
						$em = "Trop gros";
						header("Location: index.php?error=$em");
					  } else {
						$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
						$img_ex_lc = strtolower($img_ex);
	
						$allowed_exs = array("jpg", "jpeg", "png", "gif");
	
						if (in_array($img_ex_lc, $allowed_exs)) {
						  $new_img_name = uniqid("IMG-Article-", true) . '.' . $img_ex_lc;
						  $img_upload_path = '../src/Articles/IMG/' . $new_img_name;
						  move_uploaded_file($tmp_name, $img_upload_path);
	
						  // Insert into Database
	
	
							  $sql = 'INSERT INTO `article` (`article_name`, `article_desc`, `article_chapo`, `article_auteur`, `article_page`, `article_time`, `article_img`) 
									VALUES (:article_name, :article_desc, :article_chapo,  :article_auteur, :article_page, :article_time, :article_img);';
							$pdo = connexion();
							$query = $pdo->prepare($sql);
							$query->bindValue(':article_name', $_POST['article_name'], PDO::PARAM_STR);
							$query->bindValue(':article_desc', $_POST['article_desc'], PDO::PARAM_STR);
							$query->bindValue(':article_chapo', $_POST['article_chapo'], PDO::PARAM_STR);
							$query->bindValue(':article_auteur', $_POST['article_auteur'], PDO::PARAM_STR);
							$query->bindValue(':article_page', $_POST['article_page'], PDO::PARAM_STR);
							$query->bindValue(':article_time', $_POST['article_time'], PDO::PARAM_STR);
							$query->bindValue(':article_img', $img_upload_path, PDO::PARAM_STR);
							$query->execute();
							  header("Location: index.php");
						} else {
							$em = "Pas du bon type, faut essayer le type feu";
							header("Location: index.php?error=$em");
						}
					  }
					} else {
					  $em = "unknown error occurred!";
					  header("Location: index.php?error=$em");
					}
				}
			break;
			case 'article' :
				header('Location: index.php?action=update_article&article_id='.$_POST['article_to_edit']);
			break;
	
			// ELEMENTS
			case 'read_element' :
				$name = $element_unique[0]["element_name"];
				$title = $element_unique[0]["element_title"];
				$desc = $element_unique[0]["element_desc"];
				$h1 = $element_unique[0]["element_titre"];
				$chapo = $element_unique[0]["element_chapo"];
				$auteur = $element_unique[0]["element_auteur"];
	
				$view = 'element/read_element.twig';
				$data = [
					'name' => $name,
					'title' => $title,
					'desc' => $desc,
					'h1' => $h1,
					'chapo' => $chapo,
					'auteur' => $auteur,
					'contenu_element' => $elements_element_read,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
				];
			break;
			case 'create_element_balise_double' :
				$view = 'element/create_element_balise_double.twig';
				$data = [
					'articles' => $articles_readAll,
					'create_element' => 'active',
					'pages' => $pages_readAll,
					'balise' => $balise,
					'element1' => $_POST['element1'],
					'element2' => $_POST['element2'],
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'create_elemnt_balise_audio' :
				$view = 'element/create_elemnt_balise_audio.twig';
				$data = [
					'articles' => $articles_readAll,
					'create_element' => 'active',
					'pages' => $pages_readAll,
					'balise' => $balise,
					'username' => $userinfo['pseudo'],
					'image' => $_POST['audio_image'],
				];
			break;
			case 'create_element' :
				$view = 'element/create_element.twig';
				$data = [
					'articles' => $articles_readAll,
					'create_element' => 'active',
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'create_element_balise' :
				$view = 'element/create_element_balise.twig';
				$data = [
					'articles' => $articles_readAll,
					'balise' => $_POST['balise'],
					'create_element' => 'active',
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'edit_element' :
				$view = 'element/edit_element.twig';
				$data = [
					'elements' => $elements_readAll,
					'edit_element' => 'active',
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'delete_element' :
				$view = 'element/delete_element.twig';
				$data = [
					'elements' => $elements_readAll,
					'id_element' => $element_id,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'read' :
				$view = 'element/read.twig';
				$data = [
					'elements' => $elements_readAll,
					'read' => 'active',
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'element_delete' :
				$element = Element::ReadOne($element_id);
				$id_article = $element->article;
				$element = Element::delete($element_id);
				header('Location: index.php?action=read_article&article_id='.$id_article);
			break;
			case 'new_element' :
				if(isset($_POST['submit_img']) && isset($_FILES['my_image'])) {
					$img_name = $_FILES['my_image']['name'];
					$img_size = $_FILES['my_image']['size'];
					$tmp_name = $_FILES['my_image']['tmp_name'];
					$error = $_FILES['my_image']['error'];
	
					if ($error === 0) {
						if ($img_size > 12500000) {
							$em = "Trop gros";
							header("Location: index.php?error=$em");
						} else {
							$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
							$img_ex_lc = strtolower($img_ex);
							$allowed_exs = array("jpg", "jpeg", "png", "gif");
		
							if (in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("IMG-Article-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/IMG/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
		
								// Insert into Database
		
		
								$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `alt1`, `src1`, `position`, `legende1`, `credit1`, `article`)
											VALUES (:balise, :classCSS, :img_alt, :img_src, :position, :legende, :credit, :article);';
								$pdo = connexion();
								$query = $pdo->prepare($sql);
								$query->bindValue(':balise', $balise, PDO::PARAM_STR);
								$query->bindValue(':classCSS', $_POST['classCSS'], PDO::PARAM_STR);
								$query->bindValue(':img_alt', $img_name, PDO::PARAM_STR);
								$query->bindValue(':img_src', $img_upload_path, PDO::PARAM_STR);
								$query->bindValue(':legende', $_POST['legende'], PDO::PARAM_STR);
								$query->bindValue(':credit', $_POST['credit'], PDO::PARAM_STR);
								$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
								$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
								$query->execute();
								header("Location: index.php");
							} else {
								$em = "Pas du bon type, faut essayer le type feu";
								header("Location: index.php?error=$em");
							}
						}
					} else {
						$em = "unknown error occurred!";
						header("Location: index.php?error=$em");
					}
	
				} elseif(isset($_POST['submit_audio_image']) && isset($_FILES['my_audio']) && isset($_FILES['my_image_audio'])) {
	
					$img_name = $_FILES['my_audio']['name'];
					$img_size = $_FILES['my_audio']['size'];
					$tmp_name = $_FILES['my_audio']['tmp_name'];
					$error = $_FILES['my_audio']['error'];
					$img_name_audio = $_FILES['my_image_audio']['name'];
					$img_size_audio = $_FILES['my_image_audio']['size'];
					$tmp_name_audio = $_FILES['my_image_audio']['tmp_name'];
					$error_audio = $_FILES['my_image_audio']['error'];
	
					if ($error === 0) {
						if ($img_size > 12500000) {
							$em = "Trop gros";
							header("Location: index.php?error=$em");
						} else {
							$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
							$img_ex_lc = strtolower($img_ex);
							$allowed_exs = array("mp3");
							$img_ex_audio = pathinfo($img_name_audio, PATHINFO_EXTENSION);
							$img_ex_lc_audio = strtolower($img_ex_audio);
							$allowed_exs_audio = array("jpg", "jpeg", "png", "gif");
		
							if (in_array($img_ex_lc_audio, $allowed_exs_audio) AND in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("AUD-Article-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/AUDIO/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
								$new_img_name_audio = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc_audio;
								$img_upload_path_audio = '../src/Articles/AUDIO/' . $new_img_name_audio;
								move_uploaded_file($tmp_name_audio, $img_upload_path_audio);
		
								// Insert into Database
	
		
								$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `title_audio`, `src1`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `article`)
											VALUES (:balise, :classCSS1, :title_audio, :src1, :alt_media1, :src_media1, :position, :legende, :credit, :article);';
								$pdo = connexion();
								$query = $pdo->prepare($sql);
								$query->bindValue(':balise', 'audio_image', PDO::PARAM_STR);
								$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
								$query->bindValue(':title_audio', $_POST['title_audio'], PDO::PARAM_STR);
								$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
								$query->bindValue(':alt_media1', $img_name_audio, PDO::PARAM_STR);
								$query->bindValue(':src_media1', $img_upload_path_audio, PDO::PARAM_STR);
								$query->bindValue(':legende', $_POST['legende'], PDO::PARAM_STR);
								$query->bindValue(':credit', $_POST['credit'], PDO::PARAM_STR);
								$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
								$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
								$query->execute();
								header("Location: index.php");
							} else {
								$em = "Pas du bon type, faut essayer le type feu";
								header("Location: index.php?error=$em");
							}
						}
					} else {
						$em = "unknown error occurred!";
						header("Location: index.php?error=$em");
					} 
				} elseif(isset($_POST['submit_audio']) && isset($_FILES['my_audio'])) { 
					
					$img_name = $_FILES['my_audio']['name'];
					$img_size = $_FILES['my_audio']['size'];
					$tmp_name = $_FILES['my_audio']['tmp_name'];
					$error = $_FILES['my_audio']['error'];
	
					if ($error === 0) {
						if ($img_size > 12500000) {
							$em = "Trop gros";
							header("Location: index.php?error=$em");
						} else {
							$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
							$img_ex_lc = strtolower($img_ex);
							$allowed_exs = array("mp3");
		
							if (in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("AUD-Article-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/AUDIO/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
		
								// Insert into Database
	
		
								$sql = '	INSERT INTO `element` (`balise`, `title_audio`, `src1`, `position`, `legende1`, `credit1`, `article`)
											VALUES (:balise, :title_audio, :src1, :position, :legende, :credit, :article);';
								$pdo = connexion();
								$query = $pdo->prepare($sql);
								$query->bindValue(':balise', 'audio_image', PDO::PARAM_STR);
								$query->bindValue(':title_audio', $_POST['title_audio'], PDO::PARAM_STR);
								$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
								$query->bindValue(':legende', $_POST['legende'], PDO::PARAM_STR);
								$query->bindValue(':credit', $_POST['credit'], PDO::PARAM_STR);
								$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
								$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
								$query->execute();
								header("Location: index.php");
							} else {
								$em = "Pas du bon type, faut essayer le type feu";
								header("Location: index.php?error=$em");
							}
						}
					} else {
						$em = "unknown error occurred!";
						header("Location: index.php?error=$em");
					} 
				} elseif (isset($_POST['submit_video']) && isset($_FILES['my_video']) && isset($_FILES['my_image_video'])) {
	
	
					echo "<pre>";
					print_r($_FILES['my_video']);
					echo "</pre>";
		  
					$img_name = $_FILES['my_video']['name'];
					$img_size = $_FILES['my_video']['size'];
					$tmp_name = $_FILES['my_video']['tmp_name'];
					$error = $_FILES['my_video']['error'];
					$img_name_minia = $_FILES['my_image_video']['name'];
					$img_size_minia = $_FILES['my_image_video']['size'];
					$tmp_name_minia = $_FILES['my_image_video']['tmp_name'];
					$error_minia = $_FILES['my_image_video']['error'];
		  
					if ($error === 0) {
						$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
						$img_ex_lc = strtolower($img_ex);
						$allowed_exs = array("mp4");
						$img_ex_minia = pathinfo($img_name_minia, PATHINFO_EXTENSION);
						$img_ex_lc_minia = strtolower($img_ex_minia);
						$allowed_exs_minia = array("jpg", "jpeg", "png", "gif");
		  
						if (in_array($img_ex_lc_minia, $allowed_exs_minia) AND in_array($img_ex_lc, $allowed_exs)) {
						  $new_img_name = uniqid("VID-Articles-", true) . '.' . $img_ex_lc;
						  $img_upload_path = '../src/Articles/VIDEO/' . $new_img_name;
						  move_uploaded_file($tmp_name, $img_upload_path);
						  $new_img_name_minia = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc_minia;
						  $img_upload_path_minia = '../src/Articles/VIDEO/' . $new_img_name_minia;
						  move_uploaded_file($tmp_name_minia, $img_upload_path_minia);
		  
						  // Insert into Database
		  
							$sql = '	INSERT INTO `element` (`balise`, `classCSS1`,  `src1`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `article`)
										VALUES (:balise, :classCSS1, :src1, :alt_media1, :src_media1, :position, :legende, :credit, :article);';
							$pdo = connexion();
							$query = $pdo->prepare($sql);
							$query->bindValue(':balise', $balise, PDO::PARAM_STR);
							$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
							$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
							$query->bindValue(':alt_media1', $img_name_minia, PDO::PARAM_STR);
							$query->bindValue(':src_media1', $img_upload_path_minia, PDO::PARAM_STR);
							$query->bindValue(':legende', $_POST['legende'], PDO::PARAM_STR);
							$query->bindValue(':credit', $_POST['credit'], PDO::PARAM_STR);
							$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
							$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
							$query->execute();
							  header("Location: index.php");
						} else {
							$em = "Pas du bon type, faut essayer le type feu";
							header("Location: index.php?error=$em");
						}
					} else {
					  $em = "unknown error occurred!";
					  header("Location: index.php?error=$em");
					}
				} elseif(isset($_POST['submit_text'])) {
					$element = new element();
					$element->chargePOST_balise($balise);
					$element->create();
					header('Location: index.php');
				} elseif(isset($_POST['submit_content_media'])) {
					if($element1 == 'img' AND $element2 == 'img') {
						$img_name1 = $_FILES['my_image1']['name'];
						$img_size1 = $_FILES['my_image1']['size'];
						$tmp_name1 = $_FILES['my_image1']['tmp_name'];
						$error1 = $_FILES['my_image1']['error'];
						$img_name2 = $_FILES['my_image2']['name'];
						$img_size2 = $_FILES['my_image2']['size'];
						$tmp_name2 = $_FILES['my_image2']['tmp_name'];
						$error2 = $_FILES['my_image2']['error'];
	
						if ($error1 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("jpg", "jpeg", "png", "gif");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("jpg", "jpeg", "png", "gif");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/IMG/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/IMG/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `classCSS2`, `alt1`, `src1`, `alt2`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS1, :classCSS2, :alt1, :src1, :alt2, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'img_img', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':alt1', $img_name1, PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':alt2', $img_name2, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'img' AND $element2 == 'video') {
						$img_name1 = $_FILES['my_image1']['name'];
						$img_size1 = $_FILES['my_image1']['size'];
						$tmp_name1 = $_FILES['my_image1']['tmp_name'];
						$error1 = $_FILES['my_image1']['error'];
						
						$img_name2 = $_FILES['my_video2']['name'];
						$img_size2 = $_FILES['my_video2']['size'];
						$tmp_name2 = $_FILES['my_video2']['tmp_name'];
						$error2 = $_FILES['my_video2']['error'];
						
						$img_name2_video = $_FILES['my_image2_video']['name'];
						$img_size2_video = $_FILES['my_image2_video']['size'];
						$tmp_name2_video = $_FILES['my_image2_video']['tmp_name'];
						$error2_video = $_FILES['my_image2_video']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp4");
								
								$img_ex2_video = pathinfo($img_name2_video, PATHINFO_EXTENSION);
								$img_ex_lc2_video = strtolower($img_ex2_video);
								$allowed_exs2_video = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2) AND in_array($img_ex_lc2_video, $allowed_exs2_video)) {
									$new_img_name1 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/IMG/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("VID-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/VIDEO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
	
									$new_img_name2_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc2_video;
									$img_upload_path2_video = '../src/Articles/VIDEO/' . $new_img_name2_video;
									move_uploaded_file($tmp_name2_video, $img_upload_path2_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'img_video', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'img' AND $element2 == 'audio') {
						$img_name1 = $_FILES['my_image1']['name'];
						$img_size1 = $_FILES['my_image1']['size'];
						$tmp_name1 = $_FILES['my_image1']['tmp_name'];
						$error1 = $_FILES['my_image1']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
	
						if ($error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("jpg", "jpeg", "png", "gif");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/IMG/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `alt1`, `src1`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS1, :alt1, :src1, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'img_audio', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':alt1', $img_name1, PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'img' AND $element2 == 'audio_image') {
						$img_name1 = $_FILES['my_image1']['name'];
						$img_size1 = $_FILES['my_image1']['size'];
						$tmp_name1 = $_FILES['my_image1']['tmp_name'];
						$error1 = $_FILES['my_image1']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
						$img_name2_audio = $_FILES['my_image2_audio']['name'];
						$img_size2_audio = $_FILES['my_image2_audio']['size'];
						$tmp_name2_audio = $_FILES['my_image2_audio']['tmp_name'];
						$error2_audio = $_FILES['my_image2_audio']['error'];
						
						if ($error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("jpg", "jpeg", "png", "gif");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
								$img_ex2_audio = pathinfo($img_name2_audio, PATHINFO_EXTENSION);
								$img_ex_lc2_audio = strtolower($img_ex2_audio);
								$allowed_exs2_audio = array("jpg", "jpeg", "png", "gif");
								
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2) AND in_array($img_ex_lc2_audio, $allowed_exs2_audio)) {
									$new_img_name1 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/IMG/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									$new_img_name2_audio = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc2_audio;
									$img_upload_path2_audio = '../src/Articles/AUDIO/' . $new_img_name2_audio;
									move_uploaded_file($tmp_name2_audio, $img_upload_path2_audio);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `classCSS2`, `alt1`, `src1`, `src2`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS1, :classCSS2, :alt1, :src1, :src2, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'img_audio_image', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':alt1', $img_name1, PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_audio, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_audio, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'video' AND $element2 == 'img') {
						$img_name1 = $_FILES['my_video1']['name'];
						$img_size1 = $_FILES['my_video1']['size'];
						$tmp_name1 = $_FILES['my_video1']['tmp_name'];
						$error1 = $_FILES['my_video1']['error'];
						$img_name1_video = $_FILES['my_image1_video']['name'];
						$img_size1_video = $_FILES['my_image1_video']['size'];
						$tmp_name1_video = $_FILES['my_image1_video']['tmp_name'];
						$error1_video = $_FILES['my_image1_video']['error'];
						$img_name2 = $_FILES['my_image2']['name'];
						$img_size2 = $_FILES['my_image2']['size'];
						$tmp_name2 = $_FILES['my_image2']['tmp_name'];
						$error2 = $_FILES['my_image2']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp4");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("jpg", "jpeg", "png", "gif");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("VID-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/VIDEO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/IMG/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS2`, `src1`, `alt2`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS2, :src1, :alt2, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'video_img', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':alt2', $img_name2, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'video' AND $element2 == 'video') {
						$img_name1 = $_FILES['my_video1']['name'];
						$img_size1 = $_FILES['my_video1']['size'];
						$tmp_name1 = $_FILES['my_video1']['tmp_name'];
						$error1 = $_FILES['my_video1']['error'];
	
						$img_name1_video = $_FILES['my_image1_video']['name'];
						$img_size1_video = $_FILES['my_image1_video']['size'];
						$tmp_name1_video = $_FILES['my_image1_video']['tmp_name'];
						$error1_video = $_FILES['my_image1_video']['error'];
						
						$img_name2 = $_FILES['my_video2']['name'];
						$img_size2 = $_FILES['my_video2']['size'];
						$tmp_name2 = $_FILES['my_video2']['tmp_name'];
						$error2 = $_FILES['my_video2']['error'];
						
						$img_name2_video = $_FILES['my_image2_video']['name'];
						$img_size2_video = $_FILES['my_image2_video']['size'];
						$tmp_name2_video = $_FILES['my_image2_video']['tmp_name'];
						$error2_video = $_FILES['my_image2_video']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp4");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp4");
								
								$img_ex2_video = pathinfo($img_name2_video, PATHINFO_EXTENSION);
								$img_ex_lc2_video = strtolower($img_ex2_video);
								$allowed_exs2_video = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc1_video, $allowed_exs1_video) AND in_array($img_ex_lc2, $allowed_exs2) AND in_array($img_ex_lc2_video, $allowed_exs2_video)) {
									$new_img_name1 = uniqid("VID-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/VIDEO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("VID-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/VIDEO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/VIDEO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
	
									$new_img_name2_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc2_video;
									$img_upload_path2_video = '../src/Articles/VIDEO/' . $new_img_name2_video;
									move_uploaded_file($tmp_name2_video, $img_upload_path2_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media1`, `src_media1`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media1, :src_media1, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'video_video', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'video' AND $element2 == 'audio') {
						$img_name1 = $_FILES['my_video1']['name'];
						$img_size1 = $_FILES['my_video1']['size'];
						$tmp_name1 = $_FILES['my_video1']['tmp_name'];
						$error1 = $_FILES['my_video1']['error'];
	
						$img_name1_video = $_FILES['my_image1_video']['name'];
						$img_size1_video = $_FILES['my_image1_video']['size'];
						$tmp_name1_video = $_FILES['my_image1_video']['tmp_name'];
						$error1_video = $_FILES['my_image1_video']['error'];
						
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp4");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc1_video, $allowed_exs1_video) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("VID-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/VIDEO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/VIDEO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media1, :src_media1, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'video_audio', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
						$img_name1 = $_FILES['my_video1']['name'];
						$img_size1 = $_FILES['my_video1']['size'];
						$tmp_name1 = $_FILES['my_video1']['tmp_name'];
						$error1 = $_FILES['my_video1']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp4");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("VID-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/VIDEO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS`, `src1`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS, :src1, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'video_audio', PDO::PARAM_STR);
									$query->bindValue(':classCSS', '', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'video' AND $element2 == 'audio_image') {
						$img_name1 = $_FILES['my_video1']['name'];
						$img_size1 = $_FILES['my_video1']['size'];
						$tmp_name1 = $_FILES['my_video1']['tmp_name'];
						$error1 = $_FILES['my_video1']['error'];
	
						$img_name1_video = $_FILES['my_image1_video']['name'];
						$img_size1_video = $_FILES['my_image1_video']['size'];
						$tmp_name1_video = $_FILES['my_image1_video']['tmp_name'];
						$error1_video = $_FILES['my_image1_video']['error'];
						
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
						
						$img_name2_audio = $_FILES['my_image2_audio']['name'];
						$img_size2_audio = $_FILES['my_image2_audio']['size'];
						$tmp_name2_audio = $_FILES['my_image2_audio']['tmp_name'];
						$error2_audio = $_FILES['my_image2_audio']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp4");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
								
								$img_ex2_audio = pathinfo($img_name2_audio, PATHINFO_EXTENSION);
								$img_ex_lc2_audio = strtolower($img_ex2_audio);
								$allowed_exs2_audio = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc1_video, $allowed_exs1_video) AND in_array($img_ex_lc2, $allowed_exs2) AND in_array($img_ex_lc2_audio, $allowed_exs2_audio)) {
									$new_img_name1 = uniqid("VID-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/VIDEO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/VIDEO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
	
									$new_img_name2_audio = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc2_audio;
									$img_upload_path2_audio = '../src/Articles/AUDIO/' . $new_img_name2_audio;
									move_uploaded_file($tmp_name2_audio, $img_upload_path2_audio);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media1`, `src_media1`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media1, :src_media1, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'video_audio_image', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_audio, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_audio, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio' AND $element2 == 'img') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
						$img_name2 = $_FILES['my_image2']['name'];
						$img_size2 = $_FILES['my_image2']['size'];
						$tmp_name2 = $_FILES['my_image2']['tmp_name'];
						$error2 = $_FILES['my_image2']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("jpg", "jpeg", "png", "gif");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/IMG/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS2`, `src1`, `alt2`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS2, :src1, :alt2, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_img', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':alt2', $img_name2, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio' AND $element2 == 'video') {
						$img_name2 = $_FILES['my_video2']['name'];
						$img_size2 = $_FILES['my_video2']['size'];
						$tmp_name2 = $_FILES['my_video2']['tmp_name'];
						$error2 = $_FILES['my_video2']['error'];
	
						$img_name2_video = $_FILES['my_image2_video']['name'];
						$img_size2_video = $_FILES['my_image2_video']['size'];
						$tmp_name2_video = $_FILES['my_image2_video']['tmp_name'];
						$error2_video = $_FILES['my_image2_video']['error'];
						
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp4");
								
								$img_ex2_video = pathinfo($img_name2_video, PATHINFO_EXTENSION);
								$img_ex_lc2_video = strtolower($img_ex2_video);
								$allowed_exs2_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2_video, $allowed_exs2_video) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name2 = uniqid("VID-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/VIDEO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc2_video;
									$img_upload_path2_video = '../src/Articles/VIDEO/' . $new_img_name2_video;
									move_uploaded_file($tmp_name2_video, $img_upload_path2_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_video', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio' AND $element2 == 'audio') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_audio', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio' AND $element2 == 'audio_image') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
						$img_name2_audio = $_FILES['my_image2_audio']['name'];
						$img_size2_audio = $_FILES['my_image2_audio']['size'];
						$tmp_name2_audio = $_FILES['my_image2_audio']['tmp_name'];
						$error2_audio = $_FILES['my_image2_audio']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
								$img_ex2_audio = pathinfo($img_name2_audio, PATHINFO_EXTENSION);
								$img_ex_lc2_audio = strtolower($img_ex2_audio);
								$allowed_exs2_audio = array("jpg", "jpeg", "png", "gif");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									$new_img_name2_audio = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc2_audio;
									$img_upload_path2_audio = '../src/Articles/AUDIO/' . $new_img_name2_audio;
									move_uploaded_file($tmp_name2_audio, $img_upload_path2_audio);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS2`, `src1`, `src2`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS2, :src1, :src2, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_audio_image', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_audio, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_audio, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio_image' AND $element2 == 'img') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
	
						$img_name1_video = $_FILES['my_image1_audio']['name'];
						$img_size1_video = $_FILES['my_image1_audio']['size'];
						$tmp_name1_video = $_FILES['my_image1_audio']['tmp_name'];
						$error1_video = $_FILES['my_image1_audio']['error'];
						
						$img_name2 = $_FILES['my_image2']['name'];
						$img_size2 = $_FILES['my_image2']['size'];
						$tmp_name2 = $_FILES['my_image2']['tmp_name'];
						$error2 = $_FILES['my_image2']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("IMG-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/IMG/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/AUDIO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media1, :src_media1, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_image_img', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio_image' AND $element2 == 'video') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
	
						$img_name1_video = $_FILES['my_image1_audio']['name'];
						$img_size1_video = $_FILES['my_image1_audio']['size'];
						$tmp_name1_video = $_FILES['my_image1_audio']['tmp_name'];
						$error1_video = $_FILES['my_image1_audio']['error'];
						
						$img_name2 = $_FILES['my_video2']['name'];
						$img_size2 = $_FILES['my_video2']['size'];
						$tmp_name2 = $_FILES['my_video2']['tmp_name'];
						$error2 = $_FILES['my_video2']['error'];
						
						$img_name2_video = $_FILES['my_image2_video']['name'];
						$img_size2_video = $_FILES['my_image2_video']['size'];
						$tmp_name2_video = $_FILES['my_image2_video']['tmp_name'];
						$error2_video = $_FILES['my_image2_video']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp4");
								
								$img_ex2_video = pathinfo($img_name2_video, PATHINFO_EXTENSION);
								$img_ex_lc2_video = strtolower($img_ex2_video);
								$allowed_exs2_video = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("VID-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/VIDEO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/AUDIO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
	
									$new_img_name2_video = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc2_video;
									$img_upload_path2_video = '../src/Articles/VIDEO/' . $new_img_name2_video;
									move_uploaded_file($tmp_name2_video, $img_upload_path2_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `src1`, `src2`, `alt_media1`, `src_media1`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :src1, :src2, :alt_media1, :src_media1, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_image_video', PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio_image' AND $element2 == 'audio') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
						$img_name1_audio = $_FILES['my_image1_audio']['name'];
						$img_size1_audio = $_FILES['my_image1_audio']['size'];
						$tmp_name1_audio = $_FILES['my_image1_audio']['tmp_name'];
						$error1_audio = $_FILES['my_image1_audio']['error'];
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
	
						if ($error1 === 0 AND $error2 === 0) {
							if ($img_size1 > 12500000 AND $img_size2 > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								$img_ex1_audio = pathinfo($img_name1_audio, PATHINFO_EXTENSION);
								$img_ex_lc1_audio = strtolower($img_ex1_audio);
								$allowed_exs1_audio = array("jpg", "jpeg", "png", "gif");
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc2, $allowed_exs2)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									$new_img_name1_audio = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc1_audio;
									$img_upload_path1_audio = '../src/Articles/AUDIO/' . $new_img_name1_audio;
									move_uploaded_file($tmp_name1_audio, $img_upload_path1_audio);
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `src1`, `src2`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS1, :src1, :src2, :alt_media1, :src_media1, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_image_audio', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_audio, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_audio, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio_image' AND $element2 == 'audio_image') {
						$img_name1 = $_FILES['my_audio1']['name'];
						$img_size1 = $_FILES['my_audio1']['size'];
						$tmp_name1 = $_FILES['my_audio1']['tmp_name'];
						$error1 = $_FILES['my_audio1']['error'];
	
						$img_name1_video = $_FILES['my_image1_audio']['name'];
						$img_size1_video = $_FILES['my_image1_audio']['size'];
						$tmp_name1_video = $_FILES['my_image1_audio']['tmp_name'];
						$error1_video = $_FILES['my_image1_audio']['error'];
						
						$img_name2 = $_FILES['my_audio2']['name'];
						$img_size2 = $_FILES['my_audio2']['size'];
						$tmp_name2 = $_FILES['my_audio2']['tmp_name'];
						$error2 = $_FILES['my_audio2']['error'];
						
						$img_name2_video = $_FILES['my_image2_audio']['name'];
						$img_size2_video = $_FILES['my_image2_audio']['size'];
						$tmp_name2_video = $_FILES['my_image2_audio']['tmp_name'];
						$error2_video = $_FILES['my_image2_audio']['error'];
						
						if ($error1 === 0 AND $error2 === 0) {
								$img_ex1 = pathinfo($img_name1, PATHINFO_EXTENSION);
								$img_ex_lc1 = strtolower($img_ex1);
								$allowed_exs1 = array("mp3");
								
								$img_ex1_video = pathinfo($img_name1_video, PATHINFO_EXTENSION);
								$img_ex_lc1_video = strtolower($img_ex1_video);
								$allowed_exs1_video = array("jpg", "jpeg", "png", "gif");
								
								$img_ex2 = pathinfo($img_name2, PATHINFO_EXTENSION);
								$img_ex_lc2 = strtolower($img_ex2);
								$allowed_exs2 = array("mp3");
								
								$img_ex2_video = pathinfo($img_name2_video, PATHINFO_EXTENSION);
								$img_ex_lc2_video = strtolower($img_ex2_video);
								$allowed_exs2_video = array("jpg", "jpeg", "png", "gif");
								
			
								if (in_array($img_ex_lc1, $allowed_exs1) AND in_array($img_ex_lc1_video, $allowed_exs1_video) AND in_array($img_ex_lc2, $allowed_exs2) AND in_array($img_ex_lc2_video, $allowed_exs2_video)) {
									$new_img_name1 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc1;
									$img_upload_path1 = '../src/Articles/AUDIO/' . $new_img_name1;
									move_uploaded_file($tmp_name1, $img_upload_path1);
									
									$new_img_name2 = uniqid("AUD-Article-", true) . '.' . $img_ex_lc2;
									$img_upload_path2 = '../src/Articles/AUDIO/' . $new_img_name2;
									move_uploaded_file($tmp_name2, $img_upload_path2);
									
									$new_img_name1_video = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc1_video;
									$img_upload_path1_video = '../src/Articles/AUDIO/' . $new_img_name1_video;
									move_uploaded_file($tmp_name1_video, $img_upload_path1_video);
	
									$new_img_name2_video = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc2_video;
									$img_upload_path2_video = '../src/Articles/AUDIO/' . $new_img_name2_video;
									move_uploaded_file($tmp_name2_video, $img_upload_path2_video);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `classCSS2`, `src1`, `src2`, `alt_media1`, `src_media1`, `alt_media2`, `src_media2`, `position`, `legende1`, `credit1`, `legende2`, `credit2`, `article`)
												VALUES (:balise, :classCSS1, :classCSS2, :src1, :src2, :alt_media1, :src_media1, :alt_media2, :src_media2, :position, :legende1, :credit1, :legende2, :credit2, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'audio_image_audio_image', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path1, PDO::PARAM_STR);
									$query->bindValue(':src2', $img_upload_path2, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path1_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name1_video, PDO::PARAM_STR);
									$query->bindValue(':src_media2', $img_upload_path2_video, PDO::PARAM_STR);
									$query->bindValue(':alt_media2', $img_name2_video, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':legende2', $_POST['legende2'], PDO::PARAM_STR);
									$query->bindValue(':credit2', $_POST['credit2'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} else {
						echo('Une erreur est survenue');
						echo("<a href='index.php'>Revenir à la page d'accueil</a>");
					}
				} elseif(isset($_POST['submit_encadre_content'])) {
					$element = new element();
					$element->chargePOST_balise($balise);
					$element->create();
					header('Location: index.php');
				} elseif(isset($_POST['submit_encadre_media'])) {
					if($element1 == 'img') {
						$img_name = $_FILES['my_image1']['name'];
						$img_size = $_FILES['my_image1']['size'];
						$tmp_name = $_FILES['my_image1']['tmp_name'];
						$error = $_FILES['my_image1']['error'];
	
						if ($error === 0) {
							if ($img_size > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
								$allowed_exs = array("jpg", "jpeg", "png", "gif");
			
								if (in_array($img_ex_lc, $allowed_exs)) {
									$new_img_name = uniqid("IMG-Article-", true) . '.' . $img_ex_lc;
									$img_upload_path = '../src/Articles/IMG/' . $new_img_name;
									move_uploaded_file($tmp_name, $img_upload_path);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `classCSS2`, `encadre_titre`, `alt1`, `src1`, `position`, `legende1`, `credit1`, `article`)
												VALUES (:balise, :classCSS1, :classCSS2, :encadre_titre, :alt1, :src1, :position, :legende1, :credit1, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'encadre_img', PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':encadre_titre', $_POST['title_encadre'], PDO::PARAM_STR);
									$query->bindValue(':alt1', $img_name, PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio') {
						$img_name = $_FILES['my_audio1']['name'];
						$img_size = $_FILES['my_audio1']['size'];
						$tmp_name = $_FILES['my_audio1']['tmp_name'];
						$error = $_FILES['my_audio1']['error'];
	
						if ($error === 0) {
							if ($img_size > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
								$allowed_exs = array("mp3");
			
								if (in_array($img_ex_lc, $allowed_exs)) {
									$new_img_name = uniqid("AUD-Article-", true) . '.' . $img_ex_lc;
									$img_upload_path = '../src/Articles/AUDIO/' . $new_img_name;
									move_uploaded_file($tmp_name, $img_upload_path);
			
									// Insert into Database
			
			
									$sql = '	INSERT INTO `element` (`balise`, `classCSS2`, `encadre_titre`, `alt1`, `src1`, `position`, `legende1`, `credit1`, `article`)
												VALUES (:balise, :classCSS2, :encadre_titre, :alt1, :src1, :position, :legende1, :credit1, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'encadre_audio', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':encadre_titre', $_POST['title_encadre'], PDO::PARAM_STR);
									$query->bindValue(':alt1', $img_name, PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
									$query->bindValue(':legende1', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit1', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'audio_image') {
						$img_name = $_FILES['my_audio1']['name'];
						$img_size = $_FILES['my_audio1']['size'];
						$tmp_name = $_FILES['my_audio1']['tmp_name'];
						$error = $_FILES['my_audio1']['error'];
						$img_name_minia = $_FILES['my_image1_audio']['name'];
						$img_size_minia = $_FILES['my_image1_audio']['size'];
						$tmp_name_minia = $_FILES['my_image1_audio']['tmp_name'];
						$error_minia = $_FILES['my_image1_audio']['error'];
			
						if ($error === 0) {
							if ($img_size > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
								$allowed_exs = array("mp3");
								$img_ex_minia = pathinfo($img_name_minia, PATHINFO_EXTENSION);
								$img_ex_lc_minia = strtolower($img_ex_minia);
								$allowed_exs_minia = array("jpg", "jpeg", "png", "gif");
				
								if (in_array($img_ex_lc_minia, $allowed_exs_minia) AND in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("AUD-Articles-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/AUDIO/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
								$new_img_name_minia = uniqid("IMG-AUDIO-", true) . '.' . $img_ex_lc_minia;
								$img_upload_path_minia = '../src/Articles/AUDIO/' . $new_img_name_minia;
								move_uploaded_file($tmp_name_minia, $img_upload_path_minia);
				
								// Insert into Database
				
									$sql = '	INSERT INTO `element` (`balise`, `classCSS1`, `classCSS2`, `encadre_titre`, `src1`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `article`)
												VALUES (:balise, :classCSS1, :classCSS2, :encadre_titre, :src1, :alt_media1, :src_media1, :position, :legende, :credit, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'encadre_audio_image', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':classCSS1', $_POST['classCSS1'], PDO::PARAM_STR);
									$query->bindValue(':encadre_titre', $_POST['title_encadre'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name_minia, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path_minia, PDO::PARAM_STR);
									$query->bindValue(':legende', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					} elseif($element1 == 'video') {
						$img_name = $_FILES['my_video1']['name'];
						$img_size = $_FILES['my_video1']['size'];
						$tmp_name = $_FILES['my_video1']['tmp_name'];
						$error = $_FILES['my_video1']['error'];
						$img_name_minia = $_FILES['my_image1_video']['name'];
						$img_size_minia = $_FILES['my_image1_video']['size'];
						$tmp_name_minia = $_FILES['my_image1_video']['tmp_name'];
						$error_minia = $_FILES['my_image1_video']['error'];
			
						if ($error === 0) {
							if ($img_size > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
								$allowed_exs = array("mp4");
								$img_ex_minia = pathinfo($img_name_minia, PATHINFO_EXTENSION);
								$img_ex_lc_minia = strtolower($img_ex_minia);
								$allowed_exs_minia = array("jpg", "jpeg", "png", "gif");
				
								if (in_array($img_ex_lc_minia, $allowed_exs_minia) AND in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("VID-Articles-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/VIDEO/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
								$new_img_name_minia = uniqid("IMG-VIDEO-", true) . '.' . $img_ex_lc_minia;
								$img_upload_path_minia = '../src/Articles/VIDEO/' . $new_img_name_minia;
								move_uploaded_file($tmp_name_minia, $img_upload_path_minia);
				
								// Insert into Database
				
									$sql = '	INSERT INTO `element` (`balise`, `classCSS2`, `encadre_titre`, `src1`, `alt_media1`, `src_media1`, `position`, `legende1`, `credit1`, `article`)
												VALUES (:balise, :classCSS2, :encadre_titre, :src1, :alt_media1, :src_media1, :position, :legende, :credit, :article);';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':balise', 'encadre_video', PDO::PARAM_STR);
									$query->bindValue(':classCSS2', $_POST['classCSS2'], PDO::PARAM_STR);
									$query->bindValue(':encadre_titre', $_POST['title_encadre'], PDO::PARAM_STR);
									$query->bindValue(':src1', $img_upload_path, PDO::PARAM_STR);
									$query->bindValue(':alt_media1', $img_name_minia, PDO::PARAM_STR);
									$query->bindValue(':src_media1', $img_upload_path_minia, PDO::PARAM_STR);
									$query->bindValue(':legende', $_POST['legende1'], PDO::PARAM_STR);
									$query->bindValue(':credit', $_POST['credit1'], PDO::PARAM_STR);
									$query->bindValue(':position', $_POST['position'], PDO::PARAM_STR);
									$query->bindValue(':article', $_POST['article'], PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
							$em = "unknown error occurred!";
							header("Location: index.php?error=$em");
						}
					}
				}
			break;
			case 'create_element_balise_encadre' :
				$view = 'element/create_element_balise_encadre.twig';
				$data = [
					'articles' => $articles_readAll,
					'create_element' => 'active',
					'pages' => $pages_readAll,
					'balise' => $balise,
					'media' => $_POST['media'],
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'element' :
				header('Location: index.php?action=edit&element_id='.$_POST['element_to_edit']);
			break;
			case 'update' :
				if (isset($_GET['article_id'])) {
					if (isset($_POST['submit']) && isset($_FILES['article_img']) && isset($_FILES['article_vignette'])) {
	
						echo "<pre>";
						print_r($_FILES['article_img']);
						echo "</pre>";
	
						echo "<pre>";
						print_r($_FILES['article_vignette']);
						echo "</pre>";
	
						$img_name = $_FILES['article_img']['name'];
						$img_size = $_FILES['article_img']['size'];
						$tmp_name = $_FILES['article_img']['tmp_name'];
						$error = $_FILES['article_img']['error'];
	
						$img_name_vignette = $_FILES['article_vignette']['name'];
						$img_size_vignette = $_FILES['article_vignette']['size'];
						$tmp_name_vignette = $_FILES['article_vignette']['tmp_name'];
						$error_vignette = $_FILES['article_vignette']['error'];
	
						if ($error === 0 && $error_vignette === 0) {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
	
								$img_ex_vignette = pathinfo($img_name_vignette, PATHINFO_EXTENSION);
								$img_ex_lc_vignette = strtolower($img_ex_vignette);
	
								$allowed_exs = array("jpg", "jpeg", "png", "gif");
								
								$allowed_exs_vignette = array("jpg", "jpeg", "png", "gif");
	
								if (in_array($img_ex_lc, $allowed_exs) && in_array($img_ex_lc_vignette, $allowed_exs_vignette)) {
								$new_img_name = uniqid("IMG-Article-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Articles/IMG/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
	
								$new_img_name_vignette = uniqid("IMG-Article-", true) . '.' . $img_ex_lc_vignette;
								$img_upload_path_vignette = '../src/Articles/IMG/' . $new_img_name_vignette;
								move_uploaded_file($tmp_name_vignette, $img_upload_path_vignette);
	
								// Insert into Database
	
									$sql = 'UPDATE article SET article_name = :article_name, article_desc = :article_desc, article_chapo = :article_chapo, article_auteur = :article_auteur, article_time = :article_time, article_img = :article_img, article_vignette = :article_vignette WHERE id = :id;';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':id', $article_id, PDO::PARAM_INT);
									$query->bindValue(':article_name', $_POST['article_name'], PDO::PARAM_STR);
									$query->bindValue(':article_desc', $_POST['article_desc'], PDO::PARAM_STR);
									$query->bindValue(':article_chapo', $_POST['article_chapo'], PDO::PARAM_STR);
									$query->bindValue(':article_auteur', $_POST['article_auteur'], PDO::PARAM_STR);
									$query->bindValue(':article_time', $_POST['article_time'], PDO::PARAM_STR);
									$query->bindValue(':article_img', $img_upload_path, PDO::PARAM_STR);
									$query->bindValue(':article_vignette', $img_upload_path_vignette, PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								}
						}
					} else {
						// $article = new Article();
						// $article->chargePOST();
						// $article->update($article_id);
						// header('Location: index.php');
					}
				} elseif (isset($_GET['page_id'])) {
					if (isset($_POST['submit']) && isset($_FILES['page_img'])) {
	
						echo "<pre>";
						print_r($_FILES['page_img']);
						echo "</pre>";
	
						$img_name = $_FILES['page_img']['name'];
						$img_size = $_FILES['page_img']['size'];
						$tmp_name = $_FILES['page_img']['tmp_name'];
						$error = $_FILES['page_img']['error'];
	
						if ($error === 0) {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
	
								$allowed_exs = array("jpg", "jpeg", "png", "gif");
	
								if (in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("IMG-Page-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Pages/IMG/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
	
								// Insert into Database
	
									$sql = 'UPDATE page SET page_name = :page_name, page_desc = :page_desc, page_img = :page_img WHERE id = :id;';
									$pdo = connexion();
									$query = $pdo->prepare($sql);
									$query->bindValue(':id', $page_id, PDO::PARAM_INT);
									$query->bindValue(':page_name', $_POST['page_name'], PDO::PARAM_STR);
									$query->bindValue(':page_desc', $_POST['page_desc'], PDO::PARAM_STR);
									$query->bindValue(':page_img', $img_upload_path, PDO::PARAM_STR);
									$query->execute();
									header("Location: index.php");
								}
						}
					} else {
						// $page = new Page();
						// $page->chargePOST();
						// $page->update($page_id);
						// header('Location: index.php');
					}
				}
			break;
			case 'edit' :
				$view = 'element/edit.twig';
				$data = [
					'element_unique' => Element::readOne($element_id),
					'element_id' => $element_id,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
	
			// PAGES
			case 'read_page' :
				$view = 'page/read_page.twig';
				$data = [
					'unique_page' => $unique_page,
					'contenu_page' => $articles_page_read,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'create_page' :
				$view = 'page/create_page.twig';
				$data = [
					'nbr' => $count_page[0]['count_page'],
					'create_page' => 'active',
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'edit_page' :
				$view = 'page/edit_page.twig';
				$data = [
					'id_page' => $page_id,
					'articles' => $articles_readAll,
					'edit_page' => 'active',
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'edit_page_parametres' :
				$view = 'page/edit_page_parametres.twig';
				$data = [
					'id_page' => $page_id,
					'page_unique' => Page::readOne($page_id),
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'page_parametres' :
				header('Location: index.php?action=edit_page_parametres&page_id='.$_POST['page_to_edit']);
			break;
			case 'update_page' :
				$view = 'page/update_page.twig';
				$data = [
					'contenu_page' => $articles_page_read,
					'edit_page' => 'active',
					'id_page' => $page_id,
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'delete_page' :
				$view = 'page/delete_page.twig';
				$data = [
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'id_article' => $article_id,
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'read' :
				$view = 'page/read.twig';
				$data = [
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'read' => 'active',
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'page_delete' :
				$page = new Page();
				$page->delete($_POST['page_id']);
				header('Location: index.php');
			break;
			case 'new_page' :
				if (isset($_POST['submit']) && isset($_FILES['image_page'])) {
					if($count_page[0]['count_page'] < 3) {
						echo "<pre>";
						print_r($_FILES['image_page']);
						echo "</pre>";
	
						$img_name = $_FILES['image_page']['name'];
						$img_size = $_FILES['image_page']['size'];
						$tmp_name = $_FILES['image_page']['tmp_name'];
						$error = $_FILES['image_page']['error'];
	
						if ($error === 0) {
							if ($img_size > 12500000) {
								$em = "Trop gros";
								header("Location: index.php?error=$em");
							} else {
								$img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
								$img_ex_lc = strtolower($img_ex);
	
								$allowed_exs = array("jpg", "jpeg", "png", "gif");
	
								if (in_array($img_ex_lc, $allowed_exs)) {
								$new_img_name = uniqid("IMG-Page-", true) . '.' . $img_ex_lc;
								$img_upload_path = '../src/Pages/IMG/' . $new_img_name;
								move_uploaded_file($tmp_name, $img_upload_path);
	
								// Insert into Database
	
	
								$sql = 'INSERT INTO `page` (`page_name`, `page_desc`, `page_img`)
								VALUES (:page_name, :page_desc, :page_img);';
								$pdo = connexion();
								$query = $pdo->prepare($sql);
								$query->bindValue(':page_name', $_POST['page_name'], PDO::PARAM_STR);
								$query->bindValue(':page_desc', $_POST['page_desc'], PDO::PARAM_STR);
								$query->bindValue(':page_img', $img_upload_path, PDO::PARAM_STR);
								$query->execute();
									header("Location: index.php");
								} else {
									$em = "Pas du bon type, faut essayer le type feu";
									header("Location: index.php?error=$em");
								}
							}
						} else {
						  $em = "unknown error occurred!";
						  header("Location: index.php?error=$em");
						}
					} else {
						$view = "error.twig";
						$data = [
							'error' => 'Vous avez atteint le nombre de page disponnible',
						];
					}
				}
			break;
			case 'page' :
				header('Location: index.php?action=update_page&page_id='.$_POST['page_to_edit']);
			break;
	
			// INSCRIPTION / CONNEXION / DECONNEXION
			case 'create_user' :
				$view = "members/create_user.twig";
				$data = [
					'articles' => $articles_readAll,
					'pages' => $pages_readAll,
					'create_user' => 'active',
					'username' => $userinfo['pseudo'],
				];
			break;
			case 'new_user' :
				if(isset($_POST['forminscription'])) {
					$pseudo = htmlspecialchars($_POST['pseudo']);
					$mail = htmlspecialchars($_POST['mail']);
					$mail2 = htmlspecialchars($_POST['mail2']);
					$mdp = sha1($_POST['mdp']);
					$mdp2 = sha1($_POST['mdp2']);
					if(!empty($_POST['pseudo']) AND !empty($_POST['mail']) AND !empty($_POST['mail2']) AND !empty($_POST['mdp']) AND !empty($_POST['mdp2'])) {
						$pseudolength = strlen($pseudo);
						if($pseudolength <= 255) {
							  if($mail == $mail2) {
								 if(filter_var($mail, FILTER_VALIDATE_EMAIL)) {
									$pdo = connexion();
									$reqmail = $pdo->prepare("SELECT * FROM membres WHERE mail = ?");
									$reqmail->execute(array($mail));
									$mailexist = $reqmail->rowCount();
									if($mailexist == 0) {
										   if($mdp == $mdp2) {
											$insertmbr = $pdo->prepare("INSERT INTO membres(pseudo, mail, motdepasse) VALUES(?, ?, ?)");
											$insertmbr->execute(array($pseudo, $mail, $mdp));
											header('Location: index.php');
										} else {
											 $erreur = "Vos mots de passes ne correspondent pas !";
										   }
									} else {
										   $erreur = "Adresse mail déjà utilisée !";
									}
								} else {
									$erreur = "Votre adresse mail n'est pas valide !";
								}
							} else {
								$erreur = "Vos adresses mail ne correspondent pas !";
							}
						} else {
							$erreur = "Votre pseudo ne doit pas dépasser 255 caractères !";
						}
					} else {
						   $erreur = "Tous les champs doivent être complétés !";
					}
				}
			break;
			case 'disconnect' :
				session_destroy();
				echo($userinfo['pseudo']);
				header('Location: ../index.php');
			break;
	
				default:
					$view = 'accueil.twig';
					$data = [
						'accueil' => 'active',
						'articles' => $articles_readAll,
						'pages' => $pages_readAll,
						'username' => $userinfo['pseudo'],
					];
					break;
			}
	
		echo $twig->render($view, $data);
	} else {
		header('Location: ../index.php');
	}

?>