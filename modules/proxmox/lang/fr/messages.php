<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
return [
    'settings' => [
        'title' => 'Proxmox',
        'description' => 'Gérez les différentes options du module Proxmox (IPAM, Modèles, Oses).',
    ],
    'panel' => [
        'info' => 'Informations sur le VPS',
    ],
    'data' => [
        'os' => 'Système d\'exploitation',
        'security' => 'Sécurité',
    ],
    'console' => [
        'title' => 'Console',
    ],

    'logs' => [
        'title' => 'Historique',
        'description' => 'Historique des actions effectuées sur les machines virtuelles.',
        'by' => 'Fait par',
        'action' => 'Action',
        'users' => [
            'system' => 'Système',
            'user' => 'Utilisateur',
            'admin' => 'Administrateur',
        ],
        'start' => 'La commande de démarrage a été envoyée au VPS.',
        'stop' => 'La commande d\'arrêt a été envoyée au VPS',
        'restart' => 'La commande de redémarrage a été envoyée au VPS.',
        'destroy' => 'La commande de destruction a été envoyée au VPS.',
        'reinstall' => 'La commande de réinstallation a été envoyée au VPS.',
        'snapshot_destroy' => 'Le snapshot a été supprimé.',
        'snapshot_create' => 'Le snapshot a été créé.',
        'snapshot_restore' => 'Le snapshot a été restauré.',
        'backup_create' => 'La sauvegarde a été créée.',
        'backup_destroy' => 'La sauvegarde a été supprimée.',
        'reinstall_done' => 'La réinstallation de la machine virtuelle a été effectuée avec succès.',
    ],
    'graphs' => [
        'title' => 'Graphiques',
        'cpu' => 'CPU',
        'memory' => 'Mémoire',
        'disk' => 'Disque',
        'network' => 'Réseau',
        'timeframes' => [
            'hour' => 'Dernière heure',
            'day' => 'Dernier jour',
            'week' => 'Dernière semaine',
            'month' => 'Dernier mois',
            'year' => 'Dernière année',
        ],
    ],
    'reinstallation' => [
        'title' => 'Réinstallation',
        'description' => 'Réinstaller la machine virtuelle.',
        'limited' => 'Vous avez atteint le nombre maximum de réinstallations.',
        'limit_text' => 'Vous avez fait :current sur les :max autorisées.',
        'submit' => 'Réinstaller',
        'reinstall_success' => 'La machine virtuelle a été réinstallée avec succès.',
        'reinstall_error' => 'Une erreur est survenue lors de la réinstallation de la machine virtuelle.',
        'confirm' => 'Êtes-vous sûr de vouloir réinstaller cette machine virtuelle ?',
        'success' => 'La machine virtuelle a été réinstallée avec succès.',
    ],
    'sshkeys' => 'Clés SSH',
    'hostname' => 'Nom d\'hôte',
    'server' => 'Serveur',
    'node' => 'Noeud',
    'disk_storage' => 'Stockage du disque',
    'unprivileged' => 'Unprivileged (LXC uniquement)',
    'username' => 'Nom d\'utilisateur SSH',
    'uptime' => 'Temps de fonctionnement',
    'features' => 'Features (LXC uniquement)',
    'max_snapshots' => 'Nombre maximum de snapshots',
    'max_backups' => 'Nombre maximum de sauvegardes',
    'max_reinstall' => 'Nombre maximum de réinstallations',
    'max_helpers' => 'Vous pouvez définir -1 pour illimité ou 0 pour désactiver',
    'clear_cache_if_empty' => 'Vider le cache si les champs sont vides ou vérifiez que votre clé d\'API Proxmox NE DOIT PAS être en Privilege Separation.',
    'virtualisation_type' => 'Type de virtualisation',
    'importinfo' => 'Ces informations sont utilisées pour pouvoir réinstaller la machine plus facilement par la suite.',
    'templates' => [
        'title' => 'Modèles',
        'description' => 'Gestion des modèles de machines virtuelles utilisables pour la livraison de VPS.',
        'name' => 'Nom',
        'os' => 'Système d\'exploitation',
        'vmids' => 'ID de la machine virtuelle',
        'create' => [
            'title' => 'Créer un modèle',
            'description' => 'Créer un nouveau modèle de machine virtuelle.',
        ],
        'show' => [
            'title' => 'Détails du modèle',
            'description' => 'Détails du modèle de machine virtuelle.',
        ],
    ],
    'oses' => [
        'title' => 'Systèmes d\'exploitation',
        'description' => 'Gestion des systèmes d\'exploitation utilisables pour la livraison de VPS.',
        'name' => 'Nom',
        'oses' => 'Systèmes d\'exploitation',
        'create' => [
            'title' => 'Créer un système d\'exploitation',
            'description' => 'Créer un nouveau système d\'exploitation.',
        ],
        'show' => [
            'title' => 'Détails du système d\'exploitation',
            'description' => 'Détails du système d\'exploitation.',
        ],
    ],
    'ipam' => [
        'title' => 'IPAM',
        'description' => 'Gestionnaire d\'adresses IP du système Proxmox.',
        'ip' => 'Adresse IP',
        'netmask' => 'Masque de sous-réseau',
        'gateway' => 'Passerelle',
        'bridge' => 'Pont (Bridge)',
        'mtu' => 'MTU',
        'mac' => 'Adresse MAC',
        'ipv6' => 'Adresse IPv6',
        'ipv6_gateway' => 'Passerelle IPv6',
        'is_primary' => 'Primaire',
        'service_id' => 'Service',
        'notes' => 'Notes',
        'dhcp_help' => 'Vous pouvez mettre "dhcp" dans ce champ pour activer le DHCP pour cette adresse IP.',
        'create' => [
            'title' => 'Créer une adresse IP',
            'description' => 'Créer une nouvelle adresse IP dans l\'IPAM.',
            'ranges' => [
                'title' => 'Ajout rapide d\'adressess IP',
                'block' => 'Bloc',
                'block_help' => 'Il doit contenir ".XX" qui sera remplace par l\'addresse.',
                'description' => 'Importez facilement des ranges d\'addresse dans votre IPAM.',
                'range' => 'Range',
                'success' => ':count adresses IP ont été ajoutées avec succès.',
                'import' => 'Importer',
            ],
        ],
        'show' => [
            'title' => 'Détails de l\'adresse IP',
            'description' => 'Détails de l\'adresse IP.',
        ],
        'states' => [
            'available' => [
                'title' => 'Disponible',
                'mass_action' => 'Définir comme disponible',
                'description' => 'Cette adresse IP est disponible pour être utilisée.',
            ],
            'used' => [
                'title' => 'Utilisé',
                'mass_action' => 'Définir comme utilisé',
                'description' => 'Cette adresse IP est utilisée par un service.',
            ],
            'unavailable' => [
                'title' => 'Indisponible',
                'mass_action' => 'Définir comme indisponible',
                'description' => 'Cette adresse IP est indisponible pour être utilisée.',
            ],
        ]
    ],
];
