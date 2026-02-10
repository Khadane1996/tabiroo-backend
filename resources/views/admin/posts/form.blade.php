@csrf

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" name="title" id="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $post->title ?? '') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug (URL)</label>
                    <input type="text" name="slug" id="slug"
                        class="form-control @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $post->slug ?? '') }}"
                        placeholder="la-restauration-a-domicile">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label">Résumé</label>
                    <textarea name="excerpt" id="excerpt" rows="3"
                        class="form-control @error('excerpt') is-invalid @enderror" required>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    @error('excerpt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Contenu</label>
                    <textarea name="content" id="content" rows="10"
                        class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $post->content ?? '') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label for="author_name" class="form-label">Auteur</label>
                    <input type="text" name="author_name" id="author_name"
                        class="form-control @error('author_name') is-invalid @enderror"
                        value="{{ old('author_name', $post->author_name ?? '') }}">
                    @error('author_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image principale</label>
                    <input type="file" name="image" id="image"
                        class="form-control @error('image') is-invalid @enderror"
                        {{ isset($post) && $post->image_path ? '' : 'required' }}
                        accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if (!empty($post?->image_path))
                        <div class="mt-2">
                            <p class="small text-muted mb-1">Image actuelle :</p>
                            <img src="{{ asset($post->image_path) }}" alt="Image actuelle" class="img-fluid rounded"
                                style="max-height: 150px;">
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="published_at" class="form-label">Date de publication</label>
                    <input type="datetime-local" name="published_at" id="published_at"
                        class="form-control @error('published_at') is-invalid @enderror" required
                        value="{{ old('published_at', isset($post) && isset($post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                    @error('published_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status"
                        class="form-select @error('status') is-invalid @enderror" required>
                        @php
                            $status = old('status', $post->status ?? 'draft');
                        @endphp
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Publié</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                        value="1"
                        {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_featured">Mettre en avant (A la une)</label>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title">SEO (optionnel)</h6>

                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta title</label>
                    <input type="text" name="meta_title" id="meta_title"
                        class="form-control @error('meta_title') is-invalid @enderror"
                        value="{{ old('meta_title', $post->meta_title ?? '') }}">
                    @error('meta_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta description</label>
                    <textarea name="meta_description" id="meta_description" rows="3"
                        class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

