<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permissions ---
        $permissions = [
            // Plaintes
            'complaints.create',
            'complaints.view_own_region',
            'complaints.view_all',
            'complaints.edit',
            'complaints.resolve',
            'complaints.escalate',
            'complaints.delete',
            'complaints.export',

            // VBG
            'vbg.receive',
            'vbg.view_decrypted',
            'vbg.manage_enquiry',
            'vbg.notify_worldbank',
            'vbg.receive_direct',
            'vbg.view_services',

            // Comités
            'committees.view',
            'committees.manage',
            'committees.evaluate',

            // Sites
            'sites.view_all',
            'sites.view_own_region',
            'sites.create',
            'sites.update',
            'sites.delete',
            'sites.assign_contractor',
            'sites.view_risk_dashboard',

            // Travailleurs
            'workers.view',
            'workers.manage',

            // Médiation
            'mediation.create',
            'mediation.view',

            // Formulaires ODK
            'forms.view',
            'forms.manage',

            // Rapports / Statistiques
            'reports.generate',
            'reports.export',
            'statistics.view',
            'statistics.view_vbg_aggregated',

            // Communication
            'communication.view',
            'communication.manage',

            // Documentation
            'documentation.view',
            'documentation.manage',

            // Paramétrage
            'settings.view',
            'settings.manage',

            // Utilisateurs / Rôles
            'users.view',
            'users.manage',
            'roles.view',
            'roles.manage',

            // Entreprises
            'companies.manage',

            // Audit
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // --- Rôles + attribution des permissions ---

        // super_admin - tout accès
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // coordonnateur_ugp
        $coordUgp = Role::firstOrCreate(['name' => 'coordonnateur_ugp', 'guard_name' => 'web']);
        $coordUgp->syncPermissions([
            'complaints.create', 'complaints.view_all', 'complaints.view_own_region',
            'complaints.edit', 'complaints.resolve', 'complaints.escalate', 'complaints.export',
            'vbg.receive', 'vbg.view_decrypted', 'vbg.manage_enquiry', 'vbg.notify_worldbank',
            'committees.view', 'committees.manage', 'committees.evaluate',
            'sites.view_all', 'sites.create', 'sites.update', 'sites.assign_contractor', 'sites.view_risk_dashboard',
            'workers.view', 'workers.manage',
            'mediation.create', 'mediation.view',
            'forms.view', 'forms.manage',
            'reports.generate', 'reports.export',
            'statistics.view', 'statistics.view_vbg_aggregated',
            'communication.view', 'communication.manage',
            'documentation.view',
            'settings.view',
            'users.view',
            'audit.view',
        ]);

        // specialiste_vbg
        $specVbg = Role::firstOrCreate(['name' => 'specialiste_vbg', 'guard_name' => 'web']);
        $specVbg->syncPermissions([
            'vbg.receive', 'vbg.view_decrypted', 'vbg.manage_enquiry', 'vbg.notify_worldbank',
            'complaints.view_all',
            'committees.view',
            'sites.view_all', 'sites.view_risk_dashboard',
            'reports.generate', 'reports.export',
            'statistics.view', 'statistics.view_vbg_aggregated',
            'documentation.view',
            'settings.view',
            'audit.view',
        ]);

        // specialiste_sauvegarde_env
        $specEnv = Role::firstOrCreate(['name' => 'specialiste_sauvegarde_env', 'guard_name' => 'web']);
        $specEnv->syncPermissions([
            'complaints.view_all', 'complaints.view_own_region', 'complaints.resolve', 'complaints.escalate',
            'committees.view',
            'sites.view_all', 'sites.create', 'sites.update', 'sites.assign_contractor', 'sites.view_risk_dashboard',
            'reports.generate', 'reports.export',
            'statistics.view',
            'documentation.view',
        ]);

        // specialiste_sauvegarde_sociale
        $specSoc = Role::firstOrCreate(['name' => 'specialiste_sauvegarde_sociale', 'guard_name' => 'web']);
        $specSoc->syncPermissions([
            'complaints.create', 'complaints.view_all', 'complaints.view_own_region',
            'complaints.edit', 'complaints.resolve', 'complaints.escalate', 'complaints.export',
            'committees.view', 'committees.manage',
            'sites.view_all', 'sites.create', 'sites.update', 'sites.assign_contractor', 'sites.view_risk_dashboard',
            'workers.view', 'workers.manage',
            'mediation.create', 'mediation.view',
            'reports.generate', 'reports.export',
            'statistics.view',
            'documentation.view',
        ]);

        // responsable_se
        $responsableSe = Role::firstOrCreate(['name' => 'responsable_se', 'guard_name' => 'web']);
        $responsableSe->syncPermissions([
            'complaints.view_all', 'complaints.export',
            'committees.view', 'committees.evaluate',
            'sites.view_all',
            'reports.generate', 'reports.export',
            'statistics.view',
            'documentation.view',
        ]);

        // responsable_communication
        $respComm = Role::firstOrCreate(['name' => 'responsable_communication', 'guard_name' => 'web']);
        $respComm->syncPermissions([
            'communication.view', 'communication.manage',
            'complaints.view_all',
            'statistics.view',
            'documentation.view',
        ]);

        // chef_urc - filtre géographique région
        $chefUrc = Role::firstOrCreate(['name' => 'chef_urc', 'guard_name' => 'web']);
        $chefUrc->syncPermissions([
            'complaints.view_own_region', 'complaints.escalate', 'complaints.export',
            'committees.view',
            'sites.view_own_region', 'sites.view_risk_dashboard',
            'reports.generate', 'reports.export',
            'statistics.view',
            'documentation.view',
        ]);

        // president_ccgp - filtre commune
        $presidentCcgp = Role::firstOrCreate(['name' => 'president_ccgp', 'guard_name' => 'web']);
        $presidentCcgp->syncPermissions([
            'complaints.create', 'complaints.view_own_region',
            'complaints.resolve', 'complaints.escalate',
            'committees.view', 'committees.evaluate',
            'sites.view_own_region',
            'mediation.create', 'mediation.view',
            'reports.generate',
            'documentation.view',
        ]);

        // president_cbgp - filtre localité
        $presidentCbgp = Role::firstOrCreate(['name' => 'president_cbgp', 'guard_name' => 'web']);
        $presidentCbgp->syncPermissions([
            'complaints.create', 'complaints.view_own_region',
            'complaints.resolve', 'complaints.escalate',
            'committees.view',
            'sites.view_own_region',
            'mediation.create', 'mediation.view',
            'documentation.view',
        ]);

        // point_focal_vbg
        $pointFocalVbg = Role::firstOrCreate(['name' => 'point_focal_vbg', 'guard_name' => 'web']);
        $pointFocalVbg->syncPermissions([
            'vbg.receive',
            'vbg.view_services',
            'documentation.view',
        ]);

        // charge_enregistrement
        $chargeEnreg = Role::firstOrCreate(['name' => 'charge_enregistrement', 'guard_name' => 'web']);
        $chargeEnreg->syncPermissions([
            'complaints.create',
            'complaints.view_own_region',
            'documentation.view',
        ]);

        // prestataire_ong_vbg
        $prestataireOng = Role::firstOrCreate(['name' => 'prestataire_ong_vbg', 'guard_name' => 'web']);
        $prestataireOng->syncPermissions([
            'vbg.receive',
            'vbg.view_services',
            'vbg.manage_enquiry',
            'documentation.view',
        ]);

        // personne_confiance_vbg
        $personneConfiance = Role::firstOrCreate(['name' => 'personne_confiance_vbg', 'guard_name' => 'web']);
        $personneConfiance->syncPermissions([
            'vbg.receive_direct',
            'vbg.view_services',
        ]);

        // entreprise_travaux
        $entrepriseTravaux = Role::firstOrCreate(['name' => 'entreprise_travaux', 'guard_name' => 'web']);
        $entrepriseTravaux->syncPermissions([
            'workers.view',
            'documentation.view',
        ]);

        // auditeur - lecture seule
        $auditeur = Role::firstOrCreate(['name' => 'auditeur', 'guard_name' => 'web']);
        $auditeur->syncPermissions([
            'complaints.view_all',
            'committees.view',
            'sites.view_all',
            'workers.view',
            'reports.generate', 'reports.export',
            'statistics.view',
            'audit.view',
            'documentation.view',
        ]);
    }
}
