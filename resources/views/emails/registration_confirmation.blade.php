<h2>Inscrição confirmada</h2>
<p>Olá, {{ $registration->full_name ?? 'participante' }}!</p>
<p>Sua inscrição no evento <b>{{ $event->name }}</b> foi registrada.</p>
<p>Status: <b>{{ $registration->status }}</b></p>
<p>Protocolo: <b>{{ $registration->id }}</b></p>
