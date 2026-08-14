#!/usr/bin/env python3
"""Add your latest LinkedIn post to the site's Perspective section.

Usage:
  scripts/post_linkedin.py "<linkedin-post-url>" "<short excerpt>" [--date YYYY-MM-DD] [--no-push]

Updates assets/data/linkedin-posts.json (keeping only the 2 most recent
posts), then commits and pushes to the current branch. A push to main
triggers the existing "Deploy to DreamHost" GitHub Actions workflow, so
the site updates automatically within a minute or two.
"""
import argparse
import datetime
import json
import pathlib
import re
import subprocess
import sys

REPO_ROOT = pathlib.Path(__file__).resolve().parent.parent
DATA_FILE = REPO_ROOT / "assets" / "data" / "linkedin-posts.json"


def main():
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("url", help="Full LinkedIn post URL")
    parser.add_argument("excerpt", help="Short excerpt/hook text (1-2 sentences)")
    parser.add_argument("--date", help="Post date YYYY-MM-DD (default: today)")
    parser.add_argument("--no-push", action="store_true", help="Update the JSON file only; skip commit/push")
    args = parser.parse_args()

    if not re.match(r"^https://([a-z]+\.)?linkedin\.com/", args.url):
        sys.exit("Error: that doesn't look like a linkedin.com URL.")

    date = args.date or datetime.date.today().isoformat()
    if not re.match(r"^\d{4}-\d{2}-\d{2}$", date):
        sys.exit("Error: --date must be in YYYY-MM-DD format.")

    DATA_FILE.parent.mkdir(parents=True, exist_ok=True)
    posts = json.loads(DATA_FILE.read_text()) if DATA_FILE.exists() else []

    posts = [p for p in posts if p.get("url") != args.url]
    posts.insert(0, {"url": args.url, "excerpt": args.excerpt.strip(), "date": date})
    posts = posts[:2]

    DATA_FILE.write_text(json.dumps(posts, indent=2) + "\n")
    print(f"Updated {DATA_FILE.relative_to(REPO_ROOT)} with {len(posts)} post(s).")

    if args.no_push:
        print("Skipped commit/push (--no-push). Review the diff and commit manually when ready.")
        return

    subprocess.run(["git", "-C", str(REPO_ROOT), "add", str(DATA_FILE)], check=True)
    commit = subprocess.run(
        ["git", "-C", str(REPO_ROOT), "commit", "-m", f"Update LinkedIn posts ({date})"],
        cwd=REPO_ROOT,
    )
    if commit.returncode != 0:
        print("Nothing to commit (posts unchanged).")
        return

    branch = subprocess.run(
        ["git", "-C", str(REPO_ROOT), "rev-parse", "--abbrev-ref", "HEAD"],
        check=True, capture_output=True, text=True,
    ).stdout.strip()

    subprocess.run(["git", "-C", str(REPO_ROOT), "push", "-u", "origin", branch], check=True)
    print(f"Pushed to origin/{branch}.")
    if branch == "main":
        print("This triggers the DreamHost deploy workflow — the site updates within a minute or two.")
    else:
        print("Merge this branch into main to trigger the DreamHost deploy workflow.")


if __name__ == "__main__":
    main()
