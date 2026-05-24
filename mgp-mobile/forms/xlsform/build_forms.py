#!/usr/bin/env python3
"""
PACIPA-MGP - Script de compilation des XLSForms F01–F07
========================================================
Convertit les fichiers CSV (survey.csv, choices.csv, settings.csv)
en fichiers XLSForm (.xlsx) puis en XForm XML utilisables par ODK Collect.

Prérequis :
    pip install pyxform openpyxl

Usage :
    python build_forms.py              # Compile tous les formulaires
    python build_forms.py f01          # Compile seulement F01
    python build_forms.py --validate   # Valide les formulaires existants
"""

import os
import csv
import sys
import subprocess
from pathlib import Path

try:
    import openpyxl
except ImportError:
    print("ERROR: openpyxl manquant. Exécuter : pip install openpyxl pyxform")
    sys.exit(1)

BASE_DIR  = Path(__file__).parent
FORMS     = ["f01", "f02", "f03", "f04", "f05", "f06", "f07"]
XFORM_DIR = BASE_DIR.parent / "xform"

FORM_NAMES = {
    "f01": "pacipa_mgp_f01_depot_plainte_v1.xlsx",
    "f02": "pacipa_mgp_f02_plainte_non_sensible_v1.xlsx",
    "f03": "pacipa_mgp_f03_vbg_eas_hs_v1.xlsx",
    "f04": "pacipa_mgp_f04_pv_conciliation_v1.xlsx",
    "f05": "pacipa_mgp_f05_suivi_periodique_v1.xlsx",
    "f06": "pacipa_mgp_f06_pv_installation_comite_v1.xlsx",
    "f07": "pacipa_mgp_f07_evaluation_trimestrielle_v1.xlsx",
}


def csv_to_sheet(ws, csv_path: Path):
    """Charge un CSV dans une feuille Excel."""
    with open(csv_path, encoding="utf-8", newline="") as f:
        reader = csv.reader(f)
        for row in reader:
            ws.append(row)


def build_xlsx(form_id: str) -> Path:
    """Crée le fichier .xlsx XLSForm à partir des CSV."""
    form_dir  = BASE_DIR / form_id
    xlsx_path = BASE_DIR / FORM_NAMES[form_id]

    wb = openpyxl.Workbook()

    # Sheet 1 - survey
    ws_survey = wb.active
    ws_survey.title = "survey"
    csv_to_sheet(ws_survey, form_dir / "survey.csv")

    # Sheet 2 - choices
    ws_choices = wb.create_sheet("choices")
    csv_to_sheet(ws_choices, form_dir / "choices.csv")

    # Sheet 3 - settings
    ws_settings = wb.create_sheet("settings")
    csv_to_sheet(ws_settings, form_dir / "settings.csv")

    wb.save(xlsx_path)
    print(f"  [OK] {xlsx_path.name}")
    return xlsx_path


def compile_xform(xlsx_path: Path) -> None:
    """Compile le XLSForm .xlsx en XForm XML via pyxform."""
    XFORM_DIR.mkdir(parents=True, exist_ok=True)
    xform_out = XFORM_DIR / (xlsx_path.stem + ".xml")
    result = subprocess.run(
        ["xls2xform", str(xlsx_path), str(xform_out)],
        capture_output=True, text=True
    )
    if result.returncode == 0:
        print(f"  [XML] {xform_out.name}")
    else:
        print(f"  [WARN] pyxform erreur pour {xlsx_path.name}:")
        print("   ", result.stderr.strip()[:200])


def main():
    target_forms = FORMS
    validate_only = "--validate" in sys.argv

    if not validate_only:
        args = [a for a in sys.argv[1:] if not a.startswith("--")]
        if args:
            target_forms = [f for f in args if f in FORMS]

    print(f"\nPACIPA-MGP XLSForm Builder - {len(target_forms)} formulaire(s)\n")

    for form_id in target_forms:
        print(f"► {form_id.upper()} - {FORM_NAMES[form_id]}")
        try:
            xlsx = build_xlsx(form_id)
            compile_xform(xlsx)
        except Exception as e:
            print(f"  [ERROR] {e}")

    print("\nTerminé. Les XForm XML sont dans forms/xform/")
    print("Copier les .xml dans ODK Central ou dans le dossier /sdcard/odk/forms/ de l'appareil.")


if __name__ == "__main__":
    main()
