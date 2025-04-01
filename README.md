# 💼 Application GSB - Gestion des frais

> Projet réalisé dans le cadre du **BTS SIO SLAM – Épreuve E6**  
> Développement d'une application web interne pour l'entreprise fictive **Galaxy Swiss Bourdin**

---

## 📌 Contexte

L’entreprise GSB emploie des visiteurs médicaux en déplacement régulier.  
Afin de simplifier la gestion des notes de frais, j’ai conçu une application web permettant :

- La **déclaration** des frais en ligne (avec justificatifs)
- Le **traitement** des fiches par des comptables
- Une **gestion des utilisateurs** par des administrateurs
- Une **interface sécurisée** et adaptée à chaque rôle

---

## 🧭 Notice d’utilisation

### 🔐 Connexion

Chaque utilisateur possède un identifiant et un mot de passe en fonction de son rôle.

#### 👤 **Visiteurs**
| Email                            | Mot de passe |
|----------------------------------|--------------|
| nicolas.barbet@gsb.com           | `2`          |
| bob.epoleur@gsb.com              | `bob`        |

#### 🧾 **Comptables**
| Email                            | Mot de passe |
|----------------------------------|--------------|
| sophie.delrah@gsb.com            | `3`          |
| alice.merveille@gsb.com          | `alice`      |

#### 🛠️ **Administrateur**
| Email                            | Mot de passe |
|----------------------------------|--------------|
| miguel.janos@gsb.com             | `1`          |

---

## 🧰 Stack Technique

- **Backend** : PHP 8 / MySQL / PDO
- **Frontend** : HTML5 / CSS3 / JavaScript
- **UI** : Tailwind CSS
- **Librairies** : Chart.js / Font Awesome
- **Outils** : GitHub / VS Code / Trello / PhpMyAdmin

---

## 📁 Arborescence simplifiée

```bash
/Gestion_des_frais/
├── /Auth/           → Connexion & sécurité
├── /includes/       → Éléments partagés
├── /templates/      → Blocs HTML
├── /views/          → Interfaces par rôle
│   ├── /admin/
│   ├── /comptable/
│   └── /visiteur/
├── /justificatif/   → Stockage des fichiers
├── index.php
└── bdd.php