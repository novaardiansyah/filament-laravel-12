<?php

namespace App\Observers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
  public function updated(Setting $setting): void
  {
    $this->clearCache($setting);
  }

  public function deleted(Setting $setting): void
  {
    $this->clearCache($setting);
  }

  public function restored(Setting $setting): void
  {
    $this->clearCache($setting);
  }

  private function clearCache(Setting $setting): void
  {
    Cache::forget("setting.{$setting->key}");
  }
}
