# 💼 GSB Application – Expense Management

> Project carried out as part of the **BTS SIO SLAM – E6 Exam**  
> Development of an internal web application for the fictional company **Galaxy Swiss Bourdin**

---

## 📌 Context

The GSB company employs medical representatives who frequently travel.  
To simplify expense report management, I developed a web application that allows:

- **Submitting** expenses online (with receipts)
- **Processing** reports by accountants
- **User management** by administrators
- A **secure interface** tailored to each role

---

## 🧭 User Guide

### 🔐 Login

Each user has a username and password based on their role.

#### 👤 **Visitors**
| Email                            | Password |
|----------------------------------|----------|
| nicolas.barbet@gsb.com           | `2`      |
| bob.epoleur@gsb.com              | `bob`    |

#### 🧾 **Accountants**
| Email                            | Password |
|----------------------------------|----------|
| sophie.delrah@gsb.com            | `3`      |
| alice.merveille@gsb.com          | `alice`  |

#### 🛠️ **Administrator**
| Email                            | Password |
|----------------------------------|----------|
| miguel.janos@gsb.com             | `1`      |

---

## 🧰 Technical Stack

- **Backend**: PHP 8 / MySQL / PDO  
- **Frontend**: HTML5 / CSS3 / JavaScript  
- **UI**: Tailwind CSS  
- **Libraries**: Chart.js / Font Awesome  
- **Tools**: GitHub / VS Code / Trello / PhpMyAdmin

---

## 📁 Simplified Directory Structure

```bash
/Gestion_des_frais/
├── /Auth/           → Login & security
├── /includes/       → Shared components
├── /templates/      → HTML blocks
├── /views/          → Role-based interfaces
│   ├── /admin/
│   ├── /comptable/
│   └── /visiteur/
├── /justificatif/   → File storage
├── index.php
└── bdd.php
