<?php

    class Article {
        public $id;
        public $article_name;
        public $article_desc;
        public $article_chapo;
        public $article_auteur;
        public $article_page;
        public $article_time;
        public $article_vignette;


        static function readAll() {
            $sql= 'SELECT * FROM article';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->execute();
            $tableau = $query->fetchAll(PDO::FETCH_CLASS,'Article');
            return $tableau;
        }

        function chargePOST() {
            if (isset($_POST['article_name'])) {
              $this->article_name = $_POST['article_name'];
            } else {
              $this->article_name = '';
            }

            if (isset($_POST['article_desc'])) {
              $this->article_desc = $_POST['article_desc'];
            } else {
              $this->article_desc = '';
            }

            if (isset($_POST['article_chapo'])) {
              $this->article_chapo = $_POST['article_chapo'];
            } else {
              $this->article_chapo = '';
            }

            if (isset($_POST['article_auteur'])) {
              $this->article_auteur = $_POST['article_auteur'];
            } else {
              $this->article_auteur = '';
            }

            if (isset($_POST['article_page'])) {
              $this->article_page = $_POST['article_page'];
            } else {
              $this->article_page = '';
            }

            if (isset($_POST['article_time'])) {
              $this->article_time = $_POST['article_time'];
            } else {
              $this->article_time = '';
            }

            if (isset($_POST['article_vignette'])) {
              $this->article_vignette = $_POST['article_vignette'];
            } else {
              $this->article_vignette = '';
            }


        }

        function create() {
            $sql = 'INSERT INTO `article` (`article_name`, `article_desc`, `article_chapo`, `article_auteur`, `article_page`, `article_time`, `article_vignette`) VALUES (:article_name, :article_desc, :article_chapo,  :article_auteur, :article_page, :article_time, :article_vignette);';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':article_name', $this->article_name, PDO::PARAM_STR);
            $query->bindValue(':article_desc', $this->article_desc, PDO::PARAM_STR);
            $query->bindValue(':article_chapo', $this->article_chapo, PDO::PARAM_STR);
            $query->bindValue(':article_auteur', $this->article_auteur, PDO::PARAM_STR);
            $query->bindValue(':article_page', $this->article_page, PDO::PARAM_STR);
            $query->bindValue(':article_time', $this->article_time, PDO::PARAM_STR);
            $query->bindValue(':article_vignette', $this->article_vignette, PDO::PARAM_STR);
            $query->execute();
            $this->id = $pdo->lastInsertId();
          }

          function delete($id) {
            $sql = "DELETE FROM article WHERE id = :id; DELETE FROM element WHERE article = :id";
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_STR);
            $query->execute();
          }

          static function readOne($id) {
            $sql = "SELECT * FROM article WHERE id = :article_id";
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':article_id', $id, PDO::PARAM_STR);
            $query->execute();
            $tableau = $query->fetchObject('Article');
            return $tableau;
          }

          static function readArticleHeader($id) {
            $sql = 'SELECT * FROM article INNER JOIN page ON page.id = article.article_page WHERE article.id = :id';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $tableau = $query->fetchObject('Article');
            return $tableau;
          }


          function update($id) {
            $sql = 'UPDATE article SET article_name = :article_name, article_desc = :article_desc, article_chapo = :article_chapo, article_auteur = :article_auteur, article_page = :article_page, article_time = :article_time, article_vignette = :article_vignette WHERE id = :id;';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':article_name', $this->article_name, PDO::PARAM_STR);
            $query->bindValue(':article_desc', $this->article_desc, PDO::PARAM_STR);
            $query->bindValue(':article_chapo', $this->article_chapo, PDO::PARAM_STR);
            $query->bindValue(':article_auteur', $this->article_auteur, PDO::PARAM_STR);
            $query->bindValue(':article_page', $this->article_page, PDO::PARAM_STR);
            $query->bindValue(':article_time', $this->article_time, PDO::PARAM_STR);
            $query->bindValue(':article_vignette', $this->article_vignette, PDO::PARAM_STR);
            $query->execute();
          }

          static function readPage($id, $article_id) {
            $sql= 'SELECT * FROM article WHERE article.article_page = :page_id AND article.id != :article_id';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':page_id', $id, PDO::PARAM_INT);
            $query->bindValue(':article_id', $article_id, PDO::PARAM_INT);
            $query->execute();
            $tableau = $query->fetchAll(PDO::FETCH_CLASS,'Article');
            return $tableau;
        }
          static function readByPage($id) {
            $sql= 'SELECT * FROM article WHERE article.article_page = :page_id;';
            $pdo = connexion();
            $query = $pdo->prepare($sql);
            $query->bindValue(':page_id', $id, PDO::PARAM_INT);
            $query->execute();
            $tableau = $query->fetchAll(PDO::FETCH_CLASS,'Article');
            return $tableau;
        }
    }

?>