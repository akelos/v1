Comment cr�er une application simple gr�ce au framework Akelos
=========================================================

Introduction
--------------------------

Ce tutoriel va vous permettre de cr�er une application � l'aide du framework Akelos.
Nous allons donc cr�er **booklink**, une application de gestion de livres et de leurs auteurs.

Configuration n�cessaire
---------------------------

 - Une base de donn�es de type MySQL ou SQLite
 - Un serveur web Apache
 - Un acc�s shell � votre serveur ('cmd' sur Windows)
 - PHP4 or PHP5

Cette configuration est plut�t commune et se retrouve sur la plupart des serveurs web ou machines *NIX.
Bien s�r, Akelos fonctionne sur plusieurs types de configuration, mais pour ce tutoriel, nous nous concentrerons sur celle-ci.

T�l�chargement et installation
---------------------------

Tant qu'Akelos n'est pas sorti dans sa version finale (1.0), nous vous recommandons de toujours utiliser la derni�re r�vision SVN.
Pour cela, il vous faudra poss�der un client [subversion](http://subversion.tigris.org).

Pour r�cup�rer la derni�re r�vision d'Akelos, tapez la commande :

    svn co http://akelosframework.googlecode.com/svn/trunk/ akelos

Si jamais vous ne pouvez ou ne voulez pas utiliser subversion, vous pouvez toujours t�l�charger la [derni�re version stable](http://www.akelos.org/akelos_framework-dev_preview.tar.gz).
Vous pouvez ensuite l'extraire en tapant :

    tar zxvf akelos_framework-dev_preview.tar.gz ; mv akelos_framework-dev_preview akelos

Il faut maintenant s'assurer qu'Akelos sera capable d'utiliser PHP sur votre syst�me. Tapez donc :

    /usr/bin/env php -v

Si vous voyez quelque chose de ce genre :
    
    PHP 5.2.1 (cli) (built: Jan 17 2006 15:00:28)
    Copyright (c) 1997-2006 The PHP Group
    Zend Engine v2.1.0, Copyright (c) 1998-2006 Zend Technologies
    
alors vous �tes pr�ts � utiliser Akelos, vous pouvez donc passer au paragraphe suivant.

Cependant, si ce n'est pas le cas, il vous faudra trouver le chemin complet vers votre binaire PHP. En g�n�ral, il suffit de taper :

    which php

Ensuite, changez le chemin dans le shebang `#!/usr/bin/env php` par le votre, et ce, au d�but de chacun de ces fichiers :

 * script/console
 * script/generate
 * script/setup
 * script/migrate
 * script/setup
 * script/test

**Pour les utilisateurs de Windows :** Les shebang ne sont pas pris en compte sur Windows. Il vous faudra donc appeler les scripts directement avec le binaire php :

    C:\wamp\php\php.exe ./script/generate scaffold

Mise en place d'une nouvelle application Akelos
---------------------------------------------

A ce point, vous devez avoir Akelos mis en place, et devez �tre capable de lancer les scripts PHP depuis une console. Bien que ces scripts ne soient pas absolument n�cessaires au fonctionnement d'Akelos, ils le seront pour ce tutoriel.

Vous avez maintenant deux possibilit�s :

 1. Cr�er une application Akelos dans un dossier diff�rent et lier ce dernier aux librairies du Framework.
 2. Commencer � travailler directement depuis le dossier t�l�charg�, avec la s�curit� que cela implique : il n'est jamais recommand� de rendre visibles les sources de votre application.

Vous l'aurez s�rement devin�, nous utiliserons la premi�re m�thode qui consiste � cr�er un lien (symbolique par exemple) vers le dossier `public` de notre application. Il est aussi tr�s simple de configurer les dossiers du framework, puisqu'il suffit de d�finir l'emplacement de chacun des composants. Cependant, ce n'est pas le sujet de cette explication, et laissons cette partie � un prochain tutoriel expliquant la mise en place et en production d'une application.

Nous supposerons que vous avez t�l�charg� Akelos dans le dossier `HOME_DIR/akelos` et que vous vous situez � la racine du dossier `akelos`.
D'ici, vous pouvez obtenir les diff�rentes options d'installation du framework en tapant :

    ./script/setup -h

Vous devriez obtenir l'affichage suivant :

    Usage: setup [-sqphf --dependencies] <-d> 

    -deps --dependencies      Includes a copy of the framework into the application
                              directory. (true)
    -d --directory=<value>    Destination directory for installing the application.
    -f --force                Overwrite files that already exist. (false)
    -h --help                 Show this help message.
    -p --public_html=<value>  Location where the application will be accesed by the
                              webserver. ()
    -q --quiet                Suppress normal output. (false)
    -s --skip                 Skip files that already exist. (false)

Dont voici la traduction :

    Utilisation: setup [-sqphf --dependencies] <-d> 

    -deps --dependencies      Inclut une copie du framework dans le r�pertoire de
                              l'application. (true)
    -d --directory=<value>    Dossier d'installation de l'application.
    -f --force                �craser les fichiers existants. (false)
    -h --help                 Affiche cette aide.
    -p --public_html=<value>  Dossier par lequel le serveur web va acc�der �
                              l'application.
    -q --quiet                N'affiche rien. (false)
    -s --skip                 Ne copie pas les fichiers d�j� existants. (false)

Voici un exemple de commande d'installation : (remplacez `/www/htdocs` par le chemin vers votre serveur web)

    ./script/setup -d ~/booklink -p /www/htdocs/booklink

Cela va g�n�rer l'architecture suivante pour l'application **booklink** :

    booklink/
        app/ << L'application (mod�les, vues, contr�leurs, et installeurs)
        config/ << Des machins de configuration, mais tout sera fait via navigateur.
        public/ << Le seul dossier rendu public
        script/ << Outils de g�n�ration de code, de lancement de tests, etc.

**Pour les utilisateurs de Windows :** Les liens symboliques ne fonctionnent pas non plus sous Windows. Il va donc falloir renseigner Apache sur le chemin vers votre application. �ditez le fichier `httpd.conf` et rajoutez ceci (en modifiant, bien entendu, au pr�alable selon votre configuration) :

    Alias /booklink "/chemin/vers/booklink/public"

    <Directory "/chemin/vers/booklink/public">
    	Options Indexes FollowSymLinks
    	AllowOverride All
    	Order allow,deny
        Allow from all
    </Directory>

N'oubliez pas de red�marrer le serveur Apache.

### Cr�ation de la base de donn�es (MySQL) ###

**/!\ Si vous comptez utiliser SQLite, sautez cette �tape /!\\**

La prochaine �tape consiste � cr�er la base de donn�es relative � votre application.

Le but de ce tutoriel n'est bien �videmment pas de vous apprendre � cr�er une base de donn�es. Si vous ne savez pas comment faire, faites des recherches sur Google, vous trouverez s�rement quelquechose :).

Cependant, vous pouvez tout simplement essayer de cr�er 3 bases diff�rentes, pour chacun des 3 environnements (production, d�veloppement, tests)

    $> mysql -u root -p
    
    mysql> CREATE DATABASE booklink;
    mysql> CREATE DATABASE booklink_dev;
    mysql> CREATE DATABASE booklink_tests;
    
    mysql> FLUSH PRIVILEGES;
    mysql> exit

Vous pouvez bien �videmment passer par une interface graphique, telle phpMyAdmin, pour cr�er ces tables.

### Cr�ation des fichiers de configuration ###

#### � l'aide de l'installeur ####

Vous pouvez ouvrir votre navigateur et vous rendre sur le script d'installation en allant � l'adresse `http://localhost/booklink`.

Vous allez donc pouvoir configurer votre base de donn�es, vos diff�rents langages, et les permissions de vos fichiers. Le fichier de configuration sera enfin g�n�r�. Pendant que bermi s'occupe de prendre un caf� en attendant que les Anglais et les Espagnols configurent leur application **booklink**, je pencherais plut�t pour un p'tit chocolat chaud.

#### Configuration manuelle (non, pas le pr�nom) ####

Copiez les fichiers `config/DEFAULT-config.php` et `config/DEFAULT-routes.php` en tant que `config/config.php` et `config/routes.php`, respectivement, et �ditez-les � vos soins.

Il vous faudra probablement aussi d�finir le dossier � partir duquel s'effectue la r�-�criture d'URL (afin de pouvoir utiliser des URL propres). �ditez donc le fichier `public/.htaccess`, et changez la valeur de RewriteBase :

    RewriteBase /booklink

Une fois votre application install�e, vous pouvez ouvrir un navigateur et aller sur `http://localhost/booklink`. Un message d'accueil s'affichera, et vous pourrez alors supprimer les fichiers d'installation du framework.

Structure de la base de donn�es
---------------------------------

Il va maintenant falloir d�finir les tables que **booklink** va utiliser pour stocker les informations sur les livres et leurs auteurs.

La plupart du temps, lorsque l'on travaille avec d'autres d�veloppeurs, le sch�ma de la base de donn�es est susceptible de changer. Il devient alors compliqu� de maintenir cette base identique pour chaque personne du projet. Akelos propose donc une solution � ce probl�me, appel�e *installer*, ou *migration*.

Gr�ce � cet outil de migration, vous allez non seulement pouvoir cr�er vos bases de donn�es, mais aussi g�n�rer un installeur, qui pourra �tre utilis� pour enregistrer tous les diff�rents changements que vous effectuerez sur la base.

Pour ce tutoriel, cr�ez le fichier `app/installers/booklink_installer.php`, et copiez-y le contenu suivant :
 
     <?php
     
     class BooklinkInstaller extends AkInstaller
     {
         function up_1(){
             
             $this->createTable('books',
                'id,'.          // La cl� primaire
                'title,'.       // Le titre du livre
                'description,'. // La description du livre
                'author_id,'.   // L'identifiant de l'auteur. C'est gr�ce � cela qu'Akelos va pouvoir faire le lien entre les deux.
                'published_on'  // La date de publication
            );
            
             $this->createTable('authors', 
                'id,'.      // La cl� primaire
                'name'      // Le nom de l'auteur
                );
         }
         
         function down_1(){
             $this->dropTables('books','authors');
         }
     }
     
     ?>

Ce peu de donn�es suffit � Akelos pour cr�er la base de donn�es. En ne sp�cifiant que le nom des colonnes, Akelos choisira lui-m�me leur type en se basant sur les conventions de nommage des tables SQL. Cependant, vous avez bien �videmment la possibilit� de d�finir vous-m�me le typages des colonnes gr�ce � la [syntaxe de PHP Adodb](http://phplens.com/lens/adodb/docs-datadict.htm)

Maintenant que nous avons d�fini les tables, il ne reste plus qu'� les installer. Tapez la commande :

    ./script/migrate Booklink install

Et pouf ! Les tables sont install�es automagiquement ! Avec MySQL, vous devriez obtenir quelque chose du genre :

**TABLE "BOOKS"**

    +--------------+--------------+------+-----+----------------+
    | Field        | Type         | Null | Key | Extra          |
    +--------------+--------------+------+-----+----------------+
    | id           | int(11)      | NO   | PRI | auto_increment |
    | title        | varchar(255) | YES  |     |                |
    | description  | longtext     | YES  |     |                |
    | author_id    | int(11)      | YES  | MUL |                |
    | published_on | date         | YES  |     |                |
    +--------------+--------------+------+-----+----------------+ 

**TABLE "AUTHORS"**
                       
    +-------+--------------+------+-----+----------------+
    | Field | Type         | Null | Key | Extra          |
    +-------+--------------+------+-----+----------------+
    | id    | int(11)      | NO   | PRI | auto_increment |
    | name  | varchar(255) | YES  |     |                |
    +-------+--------------+------+-----+----------------+


Mod�les, Vues, et Controlleurs
------------------------------------------------------

Pour faire fonctionner vos applications, Akelos utilise le [motif de conception appel� MVC](http://fr.wikipedia.org/wiki/Motif_de_conception).

### Les conventions de nommage dans Akelos ###

Le nommage de chaque objet dans Akelos est tr�s important, puisqu'il permet l'automatisation de son fonctionnement.

#### Mod�les ####

 * **Dossier :** /app/models/
 * **Nom des classes :** au singulier, au format [CamelCase](http://fr.wikipedia.org/wiki/CamelCase) *(BankAccount, User, etc.)*
 * **Nom des fichiers :** au singulier, s�par� par des underscore *(bank_account.php.php, user.php, etc.)*
 * **Nom des tables :** au pluriel, s�par� par des underscore *(bank_accounts, users)*

#### Contr�leurs ####

 * **Dossier :** */app/controllers/*
 * **Nom des classes :** Au singulier ou au pluriel, au format CamelCase, fini par `Controller` *(AccountController, UserController)*
 * **Nom des fichiers :** Au singulier ou au pluriel, s�par� par des underscore, fini par `_controller` *(account_controller.php, user_controller.php)*

#### Vues ####

 * **Dossier :** /app/views/ + *nom_du_controller_avec_underscore/* *(app/views/account, app/views/super_user/)*
 * **Nom des fichiers :** Nom de l'action, en minuscules *(app/views/user/show.tpl)*


Utilisation du scaffolding dans Akelos
------------------------------------------

Akelos fournit une m�thode de **scaffold**, � savoir une g�n�rateur de code qui vous fera non seulement gagner du temps, mais pourra aussi servir de point de d�part � la construction de votre application, ou � votre apprentissage.

### La magie du scaffold ###

� l'aide du scaffolding, vous allez g�n�rer le squelette d'une interface d'administration pour **booklink**, ce qui va vous permettre d'ajouter/�diter/supprimer des entr�es dans la base de donn�es.
Tapez ces deux commandes :

    ./script/generate scaffold Book
    ./script/generate scaffold Author

Cela va cr�er une multitude de fichiers l� o� il le faut, et le tout va fonctionner directement ! Sceptique ?
Allez donc sur [http://localhost/booklink/author](http://localhost/booklink/author) et sur [http://localhost/booklink/books](http://localhost/booklink/books), et vous pourrez d'ores et d�j� g�rer les livres et les auteurs dans votre base de donn�es.
Allez, je vous laisse un peu de temps pour vous amuser, et revenez me voir d�s que vous �tes pr�ts � continuer.

Le fonctionnement d'Akelos
------------------------------------------

Voici une description rapide de comment Akelos r�agi lorsqu'il r�pond � l'adresse : `http://localhost/booklink/book/show/2`
  
 1. Akelos va r�cup�rer trois param�tres, en fonction de ce que vous avez d�fini dans le fichier `/config/routes.php` (tutoriel est � venir) :

  * contr�leur : *book*
  * action : *show*
  * id : 2

 2. Il va ensuite chercher le fichier `/app/controllers/book_controller.php`. S'il existe, il instanciera la classe `BookController`.

 3. Le contr�leur instanci� va chercher le mod�le lui correspondant, ici `/app/models/book.php`. Si le mod�le existe, il en cr�e une instance, disponible ici via l'attribut `$this->Book`. Il va ensuite chercher dans la base de donn�es Books l'entr�e avec un `id = 2` qui �crasera l'attribut `$this->Book`.

 4. Akelos appelle enfin l'action `show` de la classe `BookController`.

 5. A la fin de l'action, Akelos chercher le fichier de vue `/app/views/book/show.tpl` et cr�e le rendu de ce fichier, ce dernier �tant aussi disponible dans la variable `$content_for_layout` dans les layouts.

 6. Akelos va enfin chercher le fichier layout appel� `/app/views/layouts/book.tpl`. Si ce fichier est trouv�, Akelos cr�e le rendu du layout, et assigne le contenu de la vue dans `$content_for_layout`. Le tout est enfin envoy� au navigateur.

Si vous avez compris ce fonctionnement, je pense que vous pouvez d'ores et d�j� commencer � modifier votre application.

Faire la relation entre *Books* et *Authors*
----------------------------

Il va maintenant falloir cr�er le lien entre la classe *Book* et la classe *Author*. Pour cela, il vous faudra utiliser la colonne `author_id` dans la base *books*

Pour renseigner chacun des mod�les sur la relation entre *books* et *authors*, il vous suffit de faire :

`/app/models/book.php`

    <?php
    
    class Book extends ActiveRecord
    {
        var $belongs_to = 'author'; // Un livre correspond � un auteur
    }
    
    ?>

`/app/models/author.php`

    <?php
    
    class Author extends ActiveRecord
    {
        var $has_many = 'books'; // Un auteur peut poss�der plusieurs livres
    }
    
    ?>

Les mod�les savent maintenant comment ils sont li�s, mais il faut que le contr�leur `BookController` puisse charger les deux mod�les, `author` et `book`.

`/app/models/author.php`

    <?php
    
    class BookController extends ApplicationController
    {
        var $models = 'book, author'; // Cette ligne suffit � indiquer quels mod�les utiliser
        
        // ... code du controlleur
    }

La prochaine �tape consiste � afficher les auteurs disponibles dans la base lors de l'ajout/�dition d'un livre. Il suffit pour cela d'utiliser, dans la vue,
la variable `$form_options_helper`.

Juste apr�s `<?=$active_record_helper->error_messages_for('book');?>`, dans le fichier */app/views/book/_form.tpl*, rajoutez le code suivant :

`/app/views/book/_form.tpl`

    <p>
        <label for="author">_{Author}</label><br />
        <?=$form_options_helper->select('book', 'author_id', $Author->collect($Author->find(), 'name', 'id'));?>
    </p>

Si vous n'avez pas encore ajout� d'auteurs dans votre base de donn�es (vilain garnement), c'est le moment de le faire.

Vous pouvez donc d�sormais choisir l'auteur de chaque livre. C'est magnifique ! Mais vous avez s�rement remarqu� que vous ne voyez pas l'auteur des livres dans la liste des livres.
Ouvrez donc le fichier `app/views/book/show.tpl`, et juste apr�s `<? $content_columns = array_keys($Book->getContentColumns()); ?>`, rajoutez :

    <label>_{Author}:</label> <span class="static">{book.author.name?}</span><br />

Vous vous demandez s�rement ce que ces `_{Author}` ou autre `{book.author.name?}`. C'est en fait la syntaxe utilis�e par [Sintags](http://www.bermi.org/projects/sintags) dans les templates d'Akelos.


Petite conclusion
--------------------

C'est tout pour le moment. Ce tutoriel continuera bien s�r d'�voluer, et il y en aura d'autres, car ce ne sont pas l� les seules fonctionnalit�s d'Akelos !
Si vous voyez une faute de frappe ou de fran�ais, n'h�sitez pas � me le faire savoir !