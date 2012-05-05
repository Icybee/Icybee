Préambule
---------

Formulaire modèle, objet formulaire (ou formulaire) : il s'agit du formulaire WdForm

Entrée formulaire : il s'agit d'une entrée du module feedback.forms.


Le formulaire est posté
=======================

Le formulaires est posté avec l'opération 'send' et la destination 'feedback.forms'.

1. Il passe au contrôle avec le callback control_form() : on s'assure que l'identifiant de l'entrée
"formulaire" est bien présent et qu'il est valide. L'entrée "formulaire" est chargée depuis le modèle
"feedback.forms".

L'objet opération est modifié :

	- La variable 'form' contient le formulaire
	- La variable 'form_entry' contient l'entrée formulaire
	
Le control se termine par la validation du formulaire.

Une fois le contrôle terminé, on passe à la validation de l'opération.



Validation de l'opération 'send'
================================

La validation de l'opération 'send' se fait par la méthode de rappel 'validate_operation_send'. Il
ne se passe rien ici, l'opération est validée d'office. On passe donc au traitement de
l'opération.


Traitement de l'opération 'send'
================================

Le traitement de l'opération 'send' se fait par la méthode de rappel 'operation_send'.

Si le formulaire possède une méthode 'finalize' elle est appelée. Cette méthode permet de faire un
traitement des données du formulaire. Par exemple, le module "feedback.forms" se sert de la méthode
pour enregistrer les données des commentaires postés en utilisant le formulaire.

Avant d'appeler la méthode, la variable `entry` de l'objet "operation" est mise à null. Si cette
variable est définie après l'appel à la méthode `finalize` alors elle sera utilisée à la place
des paramètres de l'objet "opération" (les données du formulaire) pour la publication du message de
notification.


Notification
------------

S'il n'y a pas de méthode `finalize` ou que la méthode `finalize` renvoi un résultat qui n'est pas
vide, et que l'option de notification `is_notify` est vraie, alors un message de notification est
crée et envoyé à l'aide de la classe `WdMailer` et des paramètres de l'entrée formulaire.


Pour terminer, le resultat de l'operation 'send' est placé en session dans :

['modules']['feedback.forms']['rc'][<identifiant_entrée_formualire>]

Cette variable de session sera utilisée au prochain affichage du formulaire. Si la variable n'est
pas vide alors le message de remerciement sera affiché plutôt que le formulaire. Voir la méthode
__toString() de l'entrée formulaire. La variable est détruite après avoir été utilisée pour
afficher le message de remerciement.


Personnalisation du rendu du formulaire
=======================================

Le rendu du formulaire en code HTML peut-être personnalisé, deux évènements sont déclenchés lors
du rendu qui permettent de modifier les différentes parties du formulaire.

`render:before` Permet de modifier les valeurs `before`, `after` ainsi que le code HTML de l'objet
formulaire.

`render` Permet de modifier les éléments `before`, `after`, `form` ainsi que le rendu final qui
consiste en la concaténation de ces éléments.
