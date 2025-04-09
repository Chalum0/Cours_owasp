# Projet « MediaTek »
## État d'avancement
- TPs 01 à 03
- [ x ] CR(U)D `Book` avec validation / *sanitization*
- [ x ] CR(U)D `Illustration` avec validation / *sanitization*
- [ x ] CR(U)D `User` avec validation / *sanitization*
- [ x ] Authentification standard
- [ x ] MFA simple (au moyen de la fonction `fakeMailSend()`)
- [ ~ ] *Refactoring*
- [ ] Script `env.php` protégé
- [ ] Script `utils/db_connect.php`
- [ ] Gestion des autorisations côté *front*
- [ ] Gestion des autorisations côté *back*
- Complément (**obligatoire**)
- [ x ] Validation et *sanitization* via la fonction `filter_input()`
- [ x ] Protection CSRF
- [ x ] Gestion des exceptions liées à MySQL
- [ x ] Journalisation des erreurs *« techniques »*
- [ x ] Journalisation des accès non autorisés


## Difficultés rencontrées et solutions
Pas réussi a faire un export de la bdd car travail sur server vps (et j'ai pas trouvé...)
## Bilan des acquis
- decouverte du token csrf
- decouverte de la sanitization des données (en tout cas du terme et des pratiques autour)
- Je me suis fait un bonne idée des bonnes pratiques concernant la gestion de données dans une appli web. Merci :)
## Remarques complémentaires