<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $this->authorize('viewAny', Note::class);

        $search = trim((string) $request->input('search'));

        $notes = Note::query()
            ->where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('content', 'like', '%'.$search.'%');
                });
            })
            ->latest('updated_at')
            ->get();

        return view('notes.index', compact('notes', 'search'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string', 'max:10000'],
        ]);

        $note = Note::query()->create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        Audit::log('note.created', $note, "Nota del cuaderno creada: {$note->title}.");

        return redirect()
            ->route('notes.index')
            ->with('status', 'Nota guardada correctamente.');
    }

    public function update(Request $request, Note $note): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string', 'max:10000'],
        ]);

        $note->update($validated);

        Audit::log('note.updated', $note, "Nota del cuaderno actualizada: {$note->title}.");

        return redirect()
            ->route('notes.index')
            ->with('status', 'Nota actualizada correctamente.');
    }

    public function destroy(Note $note): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $note);

        Audit::log('note.deleted', $note, "Nota del cuaderno eliminada: {$note->title}.");

        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('status', 'Nota eliminada correctamente.');
    }
}
