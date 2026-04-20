# fix_warnings.ps1
# Corrige les patterns de warnings PHP récurrents dans ultimateimmo
# Usage: powershell -ExecutionPolicy Bypass -File fix_warnings.ps1

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$fixed = 0
$total = 0

function Fix-File($path, $replacements) {
    $content = Get-Content $path -Raw -Encoding UTF8
    $original = $content
    foreach ($r in $replacements) {
        $content = $content -replace [regex]::Escape($r.From), $r.To
    }
    if ($content -ne $original) {
        Set-Content $path $content -Encoding UTF8 -NoNewline
        Write-Host "  FIXED: $($path.Replace($root, ''))"
        return 1
    }
    return 0
}

# ─── Pattern 1 : $conf->mymodule ─────────────────────────────────────────────
Write-Host "`n[1] conf->mymodule -> conf->ultimateimmo"
Get-ChildItem $root -Recurse -Filter "*.php" | ForEach-Object {
    $total++
    $script:fixed += Fix-File $_.FullName @(
        @{ From = '$conf->mymodule->'; To = '$conf->ultimateimmo->' }
    )
}

# ─── Pattern 2 : multidir_output[$object->entity] sans fallback ──────────────
Write-Host "`n[2] multidir_output entity fallback"
Get-ChildItem $root -Recurse -Filter "*.php" | ForEach-Object {
    $script:fixed += Fix-File $_.FullName @(
        @{
            From = 'multidir_output[$object->entity]'
            To   = 'multidir_output[$object->entity > 0 ? $object->entity : $conf->entity]'
        }
    )
}

# ─── Pattern 3 : $permok sans !empty() ───────────────────────────────────────
Write-Host "`n[3] permok agenda sans !empty()"
Get-ChildItem $root -Recurse -Filter "*.php" | ForEach-Object {
    $script:fixed += Fix-File $_.FullName @(
        @{
            From = '$permok = $user->rights->agenda->myactions->create;'
            To   = '$permok = !empty($user->rights->agenda->myactions->create);'
        }
    )
}

# ─── Pattern 4 : SQL info() dans les classes — colonnes inexistantes ─────────
Write-Host "`n[4] SQL info() colonnes inexistantes dans classes"
$sqlFrom = "' fk_user_creat, fk_user_modif, fk_user_author, fk_user_valid, fk_user_cloture, datev'"
$sqlTo   = "' fk_user_creat, fk_user_modif'"

$codeFrom = @'
			if ($obj->fk_user_author)
			{
				$cuser = new User($this->db);
				$cuser->fetch($obj->fk_user_author);
				$this->user_creation = $cuser;
			}

			if ($obj->fk_user_valid)
			{
				$vuser = new User($this->db);
				$vuser->fetch($obj->fk_user_valid);
				$this->user_validation = $vuser;
			}

			if ($obj->fk_user_cloture)
			{
				$cluser = new User($this->db);
				$cluser->fetch($obj->fk_user_cloture);
				$this->user_cloture = $cluser;
			}

			$this->date_creation     = $this->db->jdate($obj->datec);
			$this->date_modification = $this->db->jdate($obj->datem);
			$this->date_validation   = $this->db->jdate($obj->datev);
'@

# Variante avec indentation 4 espaces (style immoowner)
$codeFrom2 = @'
                if ($obj->fk_user_author) {
                    $cuser               = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation = $cuser;
                }

                if ($obj->fk_user_valid) {
                    $vuser                 = new User($this->db);
                    $vuser->fetch($obj->fk_user_valid);
                    $this->user_validation = $vuser;
                }

                if ($obj->fk_user_cloture) {
                    $cluser             = new User($this->db);
                    $cluser->fetch($obj->fk_user_cloture);
                    $this->user_cloture = $cluser;
                }

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
                $this->date_validation   = $this->db->jdate($obj->datev);
'@

$codeTo = @'
			if ($obj->fk_user_creat)
			{
				$cuser               = new User($this->db);
				$cuser->fetch($obj->fk_user_creat);
				$this->user_creation = $cuser;
			}

			if ($obj->fk_user_modif)
			{
				$muser                   = new User($this->db);
				$muser->fetch($obj->fk_user_modif);
				$this->user_modification = $muser;
			}

			$this->date_creation     = $this->db->jdate($obj->datec);
			$this->date_modification = $this->db->jdate($obj->datem);
'@

$codeTo2 = @'
                if ($obj->fk_user_creat) {
                    $cuser               = new User($this->db);
                    $cuser->fetch($obj->fk_user_creat);
                    $this->user_creation = $cuser;
                }

                if ($obj->fk_user_modif) {
                    $muser                   = new User($this->db);
                    $muser->fetch($obj->fk_user_modif);
                    $this->user_modification = $muser;
                }

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
'@

Get-ChildItem "$root\class" -Filter "*.class.php" | ForEach-Object {
    $script:fixed += Fix-File $_.FullName @(
        @{ From = $sqlFrom;   To = $sqlTo   }
        @{ From = $codeFrom;  To = $codeTo  }
        @{ From = $codeFrom2; To = $codeTo2 }
    )
}

# ─── Pattern 5 : $extrafields->fetch sans assignation ────────────────────────
Write-Host "`n[5] fetch_name_optionals_label sans assignation a extralabels"
Get-ChildItem $root -Recurse -Filter "*.php" | ForEach-Object {
    $script:fixed += Fix-File $_.FullName @(
        @{
            From = '$extrafields->fetch_name_optionals_label($object->table_element);'
            To   = '$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);'
        }
    )
}

Write-Host "`n=== Terminé : $($script:fixed) fichier(s) modifié(s) ==="
Write-Host @"

Patterns NON automatisés (intervention manuelle requise) :
  - `$`permissiontoadd non définie : vérifier chaque fichier, ajouter après fetch()
  - `$`socid non défini : vérifier les fichiers _note/_document/_agenda manquants
  - `$`conf->mymodule dans immoproperty_type.class.php (url vide déjà traitée)
"@
