<?php

    class Page {
        public $id;
        public $page_name;
        public $page_desc;
        public $page_img;
        public $articles;

        static function readAll() {
            $sql= 'SELECT * FROM page';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->execute();
            $tableau = $query->fetchAll(PDO::FETCH_CLASS,'Page');
            return $tableau;
        }

        function chargePOST() {
            if (isset($_POST['page_name'])) {
              $this->page_name = $_POST['page_name'];
            } else {
              $this->page_name = '';
            }
        
            if (isset($_POST['page_desc'])) {
              $this->page_desc = $_POST['page_desc'];
            } else {
              $this->page_desc = '';
            }
        
            if (isset($_POST['page_img'])) {
              $this->page_img = $_POST['page_img'];
            } else {
              $this->page_img = '';
            }
        }

        function create() {
            $sql = 'INSERT INTO `page` (`page_name`, `page_desc`, `page_img`) VALUES (:page_name, :page_desc, :page_img);';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':page_name', $this->page_name, PDO::PARAM_STR);
            $query->bindValue(':page_desc', $this->page_desc, PDO::PARAM_STR);
            $query->bindValue(':page_img', $this->page_img, PDO::PARAM_STR);
            $query->execute();
            $this->id = $pdo->lastInsertId();
          }

          function delete($id) {
            $sql = "DELETE FROM page WHERE id = :id";
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
          }

          static function readOne($id) {
            $sql = "SELECT * FROM page WHERE id = :id";
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
            $tableau = $query->fetchObject('Page');
            return $tableau;
          }

          static function readPageHeader($id) {
            $sql = 'SELECT * FROM page WHERE id = :id';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $tableau = $query->fetchObject('Page');
            return $tableau;
          }
          

          function update($id) {
            $sql = 'UPDATE page SET page_name = :article_name, article_title = :article_title, article_desc = :article_desc, article_titre = :article_titre, article_chapo = :article_chapo, article_auteur = :article_auteur, article_page = :article_page, article_time = :article_time WHERE id = :id;';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':article_name', $this->article_name, PDO::PARAM_STR);
            $query->bindValue(':article_title', $this->article_title, PDO::PARAM_STR);
            $query->bindValue(':article_desc', $this->article_desc, PDO::PARAM_STR);
            $query->bindValue(':article_titre', $this->article_titre, PDO::PARAM_STR);
            $query->bindValue(':article_chapo', $this->article_chapo, PDO::PARAM_STR);
            $query->bindValue(':article_auteur', $this->article_auteur, PDO::PARAM_STR);
            $query->bindValue(':article_page', $this->article_page, PDO::PARAM_STR);
            $query->bindValue(':article_time', $this->article_time, PDO::PARAM_STR);
            $query->execute();
          }

          static function readCountPage() {
            $sql = "SELECT COUNT(*) AS 'count_page' FROM page";
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->execute();
            $tableau = $query->fetchAll(PDO::FETCH_CLASS, 'Page');
            return $tableau;
          }
          
    }

?>