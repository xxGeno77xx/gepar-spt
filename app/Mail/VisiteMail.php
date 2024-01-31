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

        $this->mailableEngines = Engine::Join('visites', function ($join) {
            $limite = parametre::where('options', 'Visites techniques')->value('limite');
            $join->on('engines.id', '=', 'visites.engine_id')
                ->whereRaw('visites.created_at = (SELECT MAX(created_at) FROM visites WHERE engine_id = engines.id AND visites.state =?)', [StatesClass::Activated()->value])
                ->whereRaw("DATE(visites.date_expiration)<= DATE_ADD(CURDATE(), INTERVAL  $limite DAY) ")
                ->where('visites.state', StatesClass::Activated()->value)
                ->whereNull('visites.deleted_at');
        })
            ->join('modeles', 'engines.modele_id', '=', 'modeles.id')
            // ->join('departements','engines.departement_id','departements.id')
            ->join('marques', 'modeles.marque_id', '=', 'marques.id')
            ->select('engines.*', /*'departements.nom_departement', */ 'marques.logo as logo', 'visites.date_initiale as date_initiale', DB::raw('DATE(visites.date_expiration) as date_expiration'), 'modeles.nom_modele', 'marques.nom_marque')
            ->where('engines.state', StatesClass::Activated()->value)
            ->where('engines.visites_mail_sent', '=', 0)
            ->groupBy('engines.id', 'marques.nom_marque', 'visites.date_initiale', 'visites.date_expiration')
            ->distinct('engines.id')
            ->get();

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
