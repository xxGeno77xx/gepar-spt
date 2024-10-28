<?php

namespace App\Mail;

use App\Models\Engine;
use App\Models\Parametre;
use App\Support\Database\StatesClass;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AssuranceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailableEngines;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        // $this->mailableEngines = Engine::Join('assurances', function ($join) {

        //     $limite = parametre::where('options', 'Assurances')->value('limite');

        //     $join->on('engines.id', '=', 'assurances.engine_id')
        //         ->whereRaw(
        //             'assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state =?)',
        //             [StatesClass::Activated()->value]
        //         )
        //         ->whereRaw("DATE(assurances.date_fin)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
        //         ->where('assurances.state', StatesClass::Activated()->value)
        //         ->whereNull('assurances.deleted_at');
        // })
        //     ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
        //     // ->join('departements','engines.departement_id','departements.id')
        //     ->join('marques', 'modeles.marque_id', '=', 'marques.id')
        //     ->select('engines.*', /*'departements.nom_departement',*/ 'marques.logo as logo', 'assurances.date_debut as date_debut',
        //         DB::raw('DATE(assurances.date_fin) as date_fin'),
        //         'marques.nom_marque', 'modeles.nom_modele')
        //     ->where('engines.state', StatesClass::Activated()->value)
        //     ->where('engines.assurances_mail_sent', '=', 0)
        //     ->groupBy('engines.id', 'marques.nom_marque', 'assurances.date_debut', 'assurances.date_fin')
        //     ->distinct('engines.id')->get();

        $limite = parametre::where('options', 'Assurances')->value('limite');

        $activated = StatesClass::Activated()->value;

        $this->mailableEngines = Engine::Join('assurances', 'engines.id', '=', 'assurances.engine_id')
            ->whereRaw('assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state = ?)', [$activated])
            ->whereRaw('TRUNC(assurances.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limite])
            ->where('assurances.state', $activated)
            ->where('engines.assurances_mail_sent', '=', 0)
            ->whereNull('assurances.deleted_at')
            ->whereNull('engines.deleted_at')
            // ->leftjoin('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->leftjoin('centre', 'engines.departement_id', 'centre.code_centre')
            ->leftjoin('marques', 'engines.marque_id', '=', 'marques.id')
            ->select('engines.*', 'marques.logo as logo', 'assurances.date_debut as date_debut', 'assurances.date_fin as date_fin')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'assurances.date_fin',
                'engines.distance_parcourue',
                'assurances.date_debut',
                'engines.id',
                'engines.marque_id',
                'engines.power',
                'engines.departement_id',
                'engines.price',
                'engines.circularization_date',
                'engines.date_aquisition',
                'engines.plate_number',
                'engines.type_id',
                'engines.car_document',
                'engines.carburant_id',
                'engines.assurances_mail_sent',
                'engines.visites_mail_sent',
                'engines.tvm_mail_sent',
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.circuit_id',
                'engines.pl_ass',
                'engines.matricule_precedent',
                'engines.poids_total_en_charge',
                'engines.poids_a_vide',
                'engines.poids_total_roulant',
                'engines.charge_utile',
                'engines.largeur',
                'engines.surface',
                'engines.couleur',
                'engines.date_cert_precedent',
                'engines.kilometrage_achat',
                'engines.numero_carte_grise',
                'engines.user_id',
                'engines.updated_at_user_id',
                'engines.deleted_at',
                'engines.created_at',
                'engines.updated_at',
                'sigle_centre',
                'nom_marque',
                'logo',
                'remainder'
                // 'nom_modele'

            )
            ->distinct()->get();

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerte assurance',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.mail.AssurancesMailMarkdown',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
