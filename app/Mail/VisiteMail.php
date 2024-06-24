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

class VisiteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailableEngines;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {

        // $this->mailableEngines = Engine::Join('visites', function ($join) {
        //     $limite = parametre::where('options', 'Visites techniques')->value('limite');
        //     $join->on('engines.id', '=', 'visites.engine_id')
        //         ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state =?)', [StatesClass::Activated()->value])
        //         ->whereRaw("DATE(visites.date_expiration)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
        //         ->where('visites.state', StatesClass::Activated()->value)
        //         ->whereNull('visites.deleted_at');
        // })
        //     ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
        //     // ->join('departements','engines.departement_id','departements.id')
        //     ->join('marques', 'modeles.marque_id', '=', 'marques.id')
        //     ->select('engines.*', /*'departements.nom_departement', */ 'marques.logo as logo', 'visites.date_initiale as date_initiale', DB::raw('DATE(visites.date_expiration) as date_expiration'), 'modeles.nom_modele', 'marques.nom_marque')
        //     ->where('engines.state', StatesClass::Activated()->value)
        //     ->where('engines.visites_mail_sent', '=', 0)
        //     ->groupBy('engines.id', 'marques.nom_marque', 'visites.date_initiale', 'visites.date_expiration')
        //     ->distinct('engines.id')
        //     ->get();

        $limite = parametre::where('options', 'Visites techniques')->value('limite');

        $activated = StatesClass::Activated()->value;

        $this->mailableEngines = Engine::Join('visites', 'engines.id', '=', 'visites.engine_id')
            ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state = ?)', [$activated])
            ->whereRaw('TRUNC(visites.date_expiration) <= TRUNC(SYSDATE + TRUNC(?))', [$limite])
            ->where('visites.state', $activated)
            ->whereNull('visites.deleted_at')
            ->whereNull('engines.deleted_at')
            // ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'engines.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'centre.sigle',*/ 'marques.logo as logo', 'visites.date_initiale as date_initiale', 'visites.date_expiration as date_expiration')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'visites.date_expiration',
                'visites.date_initiale',
                'engines.tvm_mail_sent',
                'engines.distance_parcourue',
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
                'engines.state',
                'engines.numero_chassis',
                'engines.moteur',
                'engines.carosserie',
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

            )
            ->distinct()->get();

       
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerte visite technique',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.mail.VisitesMailMarkdown',
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

    public function build()
    {

    }
}
