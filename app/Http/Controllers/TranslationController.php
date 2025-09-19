<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{

    public function index()
    {
        // Load translations from files
        $enTranslations = File::exists(base_path('lang/en/messages.php'))
            ? include base_path('lang/en/messages.php')
            : [];
        $bnTranslations = File::exists(base_path('lang/bn/messages.php'))
            ? include base_path('lang/bn/messages.php')
            : [];

        // Merge keys to ensure all keys are displayed
        $keys = array_unique(array_merge(
            array_keys($enTranslations),
            array_keys($bnTranslations)
        ));

        $translations = [];
        foreach ($keys as $key) {
            $translations[$key] = [
                'en' => $enTranslations[$key] ?? '',
                'bn' => $bnTranslations[$key] ?? '',
            ];
        }

        return view('translations.index', compact('translations'));
    }

    /**
     * Store a new translation key and its values.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|regex:/^[a-z0-9_]+$/|max:255',
            'en' => 'required|string',
            'bn' => 'required|string',
        ]);

        $key = $request->key;

        // Load existing translations
        $enTranslations = $this->loadTranslations('en');
        $bnTranslations = $this->loadTranslations('bn');

        // Check if key already exists
        if (array_key_exists($key, $enTranslations) || array_key_exists($key, $bnTranslations)) {
            return back()->with('error', 'This key already exists. Please use the edit functionality.');
        }

        // Add the new translation
        $enTranslations[$key] = $request->en;
        $bnTranslations[$key] = $request->bn;

        // Save to files
        $this->saveTranslations('en', $enTranslations);
        $this->saveTranslations('bn', $bnTranslations);

        // Clear cache
        Artisan::call('cache:clear');

        return redirect()->route('admin.translations.index')->with('success', 'New translation key added successfully.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'translations' => 'required|array',
            'translations.*.en' => 'nullable|string',
            'translations.*.bn' => 'nullable|string',
        ]);

        // Load existing translations
        $enTranslations = File::exists(base_path('lang/en/messages.php'))
            ? include base_path('lang/en/messages.php')
            : [];
        $bnTranslations = File::exists(base_path('lang/bn/messages.php'))
            ? include base_path('lang/bn/messages.php')
            : [];

        // Update translations based on form input
        foreach ($request->translations as $key => $translation) {
            $enTranslations[$key] = $translation['en'] ?? '';
            $bnTranslations[$key] = $translation['bn'] ?? '';
        }

        // Remove empty translations
        $enTranslations = array_filter($enTranslations, fn($value) => $value !== '');
        $bnTranslations = array_filter($bnTranslations, fn($value) => $value !== '');

        // Save to files
        $this->saveTranslations('en', $enTranslations);
        $this->saveTranslations('bn', $bnTranslations);

        // Clear translation cache
        Artisan::call('cache:clear');

        return redirect()->route('admin.translations.index')->with('success', __('Translation updated successfully'));
    }

    private function loadTranslations($locale)
    {
        $path = base_path("lang/$locale/messages.php");
        return File::exists($path) ? include $path : [];
    }

    private function saveTranslations($locale, $translations)
    {
        ksort($translations); // কী অনুযায়ী সাজিয়ে নিন
        $content = "<?php\n\nreturn [\n";
        foreach ($translations as $key => $value) {
            $content .= "    '" . addslashes($key) . "' => '" . addslashes($value) . "',\n";
        }
        $content .= "];\n";
        File::put(base_path("lang/$locale/messages.php"), $content);
    }
    /* private function saveTranslations($locale, $translations)
    {
        $content = "<?php\n\nreturn [\n";
        foreach ($translations as $key => $value) {
            $content .= "    '$key' => " . var_export($value, true) . ",\n";
        }
        $content .= "];\n";

        File::put(base_path("lang/$locale/messages.php"), $content);
    } */
}
