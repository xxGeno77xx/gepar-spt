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
    public $mailableEngines;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->mailableEngines = Engine::Join('assurances', function ($join) {

            $limite = parametre::where('options', 'Assurances')->value('limite');

            $join->on('engines.id', '=', 'assurances.engine_id')
                ->whereRaw(
                    'assurances.created_at = (SELECT MAX(created_at) FROM assurances WHERE engine_id = engines.id AND assurances.state =?)',
                    [StatesClass::Activated()->value]
                )
                ->whereRaw("DATE(assurances.date_fin)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
                ->where('assurances.state', StatesClass::Activated()->value)
                ->whereNull('assurances.deleted_at');
        })
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            // ->join('departements','engines.departement_id','departements.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'departements.nom_departement',*/ 'marques.logo as logo', 'assurances.date_debut as date_debut',
                DB::raw('DATE(assurances.date_fin) as date_fin'),
                'marques.nom_marque', 'modeles.nom_modele')
            ->where('engines.state', StatesClass::Activated()->value)
            ->where('engines.assurances_mail_sent', '=', 0)
            ->groupBy('engines.id', 'marques.nom_marque', 'assurances.date_debut', 'assurances.date_fin')
            ->distinct('engines.id')->get();

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
