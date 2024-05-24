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

class TvmMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailableEngines;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $limite = Parametre::where('options', 'Tvm')->value('limite');

        $activated = StatesClass::Activated()->value;

        $this->mailableEngines = Engine::Join('tvms', 'engines.id', '=', 'tvms.engine_id')
            ->whereRaw('tvms.created_at = (SELECT MAX(created_at) FROM tvms WHERE engine_id = engines.id AND tvms.state = ?)', [$activated])
            ->whereRaw('TRUNC(tvms.date_fin) <= TRUNC(SYSDATE + TRUNC(?))', [$limite])
            ->where('tvms.state', $activated)
            ->whereNull('tvms.deleted_at')
            ->whereNull('engines.deleted_at')
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            ->join('centre', 'engines.departement_id', 'centre.code_centre')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'centre.sigle',*/ 'marques.logo as logo', 'tvms.date_debut as date_debut', 'tvms.date_fin as date_fin')
            ->where('engines.state', '<>', StatesClass::Deactivated()->value)
            ->distinct('engines.id')
            ->groupBy(
                'tvms.date_fin',
                'tvms.date_debut',
                'engines.tvm_mail_sent',
                'engines.id',
                'engines.modele_id',
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
                'nom_modele',
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
            subject: 'Alerte Tvm ',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.mail.TvmsMailMarkdown',
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
