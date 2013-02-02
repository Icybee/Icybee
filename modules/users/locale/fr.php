<?php

return array
(
	'button' => array
	(
		'Connect' => 'Connexion'
	),

	'module_title.users' => 'Utilisateurs',

	'users' => array
	(
		'count' => array
		(
			'none' => 'Aucun utilisateur',
			'one' => 'Un utilisateur',
			'other' => ':count utilisateurs'
		),

		'name' => array
		(
			'one' => 'Utilisateur',
			'other' => 'Utilisateurs'
		)
	),

	'description' => array
	(
		'is_activated' => "Seuls les utilisateurs dont le compte est activé peuvent se connecter.",

		'password_confirm' => "Si vous avez saisi un mot de passe, veuillez le confirmer.",

		'password_new' => "Si le champs est vide lors de l'enregistrement d'un nouveau compte, un
		mot de passe est généré automatiquement. Pour le personnaliser, veuillez saisir le mot de
		passe.",

		'password_update' => "Si vous souhaitez changer de mot de passe, veuillez saisir le
		nouveau dans ce champ. Sinon, laissez-le vide.",

		'roles' => "Parce que vous en avec la permission, vous pouvez choisir le ou les rôles de
		l'utilisateur."
	),

	'users.edit.element' => array
	(
		'label.siteid' => "Restriction d'accès aux sites",
		'description.siteid' => "Permet de restraindre l'accès de l'utilisateur aux
		sites sélectionnés. Si aucun site n'est sélectionné, tous les sites lui sont accessibles.",
		'description.language' => "Il s'agit de la langue à utiliser pour l'interface."
	),

	'label' => array
	(
		'logout' => 'Déconnexion',
		'name_as' => 'Nom comme',
		'email' => 'E-mail',
		'firstname' => 'Prénom',
		'Firstname' => 'Prénom',
		'is_activated' => "Le compte de l'utilisateur est actif",
		'logged_at' => 'Connecté le',
		'lastname' => 'Nom',
		'Lastname' => 'Nom',
		'lost_password' => "J'ai oublié mon mot de passe",
		'name' => 'Nom',
		'Name' => 'Nom',
		'Nickname' => 'Surnom',
		'password' => 'Mot de passe',
		'Password' => 'Mot de passe',
		'password_confirm' => 'Confirmation',
		'Current password' => "Mot de passe actuel",
		"New password" => "Nouveau mot de passe",
		"Confirm password" => "Confirmation du mot de passe",
		'Roles' => 'Rôles',
		'Timezone' => 'Zone horaire',
		'username' => 'Identifiant',
		'Username' => 'Identifiant',
		'your_email' => 'Votre adresse e-mail'
	),

	'module_category.users' => 'Utilisateurs',

	'group.title' => array
	(
		'connection' => 'Connexion'
	),

	'users.manager.label.logged_at' => 'Connecté le',

	'permission.modify own profile' => 'Modifier son profil',

	# operation/nonce_login_request

	'nonce_login_request' => array
	(
		'already_sent' => "Un message a déjà été envoyé à votre adresse email. Afin de réduire les abus, un nouveau ne pourra être envoyé avant :time.",

		'operation' => array
		(

			'title' => 'Demander une connexion a usage unique',
			'message' => array
			(
				'subject' => "Voici un message pour vous aider à vous connecter",
				'template' => <<<EOT
Ce message a été envoyé pour vous aider à vous connecter.

En utilisant l'URL suivante vous serez en mesure de vous connecter
et de mettre à jour votre mot de passe.

:url

Cette URL est a usage unique et n'est valable que jusqu'à :until.

Si vous n'avez pas créé de profil ni demandé un nouveau mot de passe, ce message peut être le
résultat d'une tentative d'attaque sur le site. Si vous pensez que c'est le cas, merci de contacter
son administrateur.

L'adresse distante était : :ip.
EOT
			),

			'success' => "Un message pour vous aider à vous connecter a été envoyé à l'adresse %email."
		)
	),


	#
	# login
	#

	'Disconnect' => 'Déconnexion',
	'Unknown username/password combination.' => 'Combinaison Identifiant/Mdp inconnue.',
	'User %username is not activated' => "Le compte de l'utilisateur %username n'est pas actif",
	'You are connected as %username, and your role is %role.' => 'Vous êtes connecté en tant que %username, et votre rôle est %role.',
	'Administrator' => 'Administrateur',
	'My profile' => 'Mon profil',
	'User profile' => 'Profil utilisateur',
	"Password and password verify don't match." => "Le mot de passe et sa vérification ne correspondent pas.",

	#
	# resume
	#

	'Users' => 'Utilisateurs',
	'Role' => 'Rôle',
	'send a new password' => 'envoyer un nouveau mot de passe',

	#
	# management
	#

	'confirm' => 'confirmer',

	"Your profile has been created." => "Votre profil a été créé.",
	"Your profile has been updated." => "Votre profil a été mis à jour.",
	"%name's profile has been created." => "Le profil de %name a été créé.",
	"%name's profile has been updated." => "Le profil de %name a été mis à jour.",

	# operation/nonce_login_request

	"Invalid email address: %email." => "Adresse e-mail invalide : %email.",
	"Unknown email address." => "Adresse e-mail inconnue."
);