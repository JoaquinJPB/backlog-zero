#!/usr/bin/env node
// Installs the Claude Code skills and agents listed in scripts/claude-setup.json
// into .claude/ (gitignored). The manifest is versioned; the installed files are not.
//
// Manifest entry: { "name": "x", "repo": "owner/repo", "ref": "main", "path": "skills/x" }
// - skills: `path` is a directory (must contain SKILL.md) → .claude/skills/<name>/
// - agents: `path` is a .md file → .claude/agents/<name>.md

import { execFileSync } from 'node:child_process';
import {
    cpSync,
    existsSync,
    mkdirSync,
    mkdtempSync,
    readFileSync,
    rmSync,
    statSync,
} from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const MANIFEST = join(ROOT, 'scripts', 'claude-setup.json');
const CLAUDE_DIR = join(ROOT, '.claude');
const NAME_PATTERN = /^[a-z0-9][a-z0-9_-]*$/;
const REPO_PATTERN = /^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/;
const REF_PATTERN = /^[A-Za-z0-9_./-]+$/;

function fail(message) {
    console.error(`✖ ${message}`);
    process.exit(1);
}

function validateEntry(kind, entry) {
    const label = `${kind} "${entry?.name ?? '?'}"`;
    if (!NAME_PATTERN.test(entry?.name ?? ''))
        fail(`${label}: name must match ${NAME_PATTERN}`);
    if (!REPO_PATTERN.test(entry.repo ?? ''))
        fail(`${label}: repo must be "owner/repo"`);
    if (entry.ref !== undefined && !REF_PATTERN.test(entry.ref))
        fail(`${label}: invalid ref`);
    if (typeof entry.path !== 'string' || entry.path === '')
        fail(`${label}: path is required`);
}

/** Resolves `path` inside `base`, refusing anything that escapes it. */
function insideOf(base, path) {
    const full = resolve(base, path);
    const rel = relative(base, full);
    if (rel.startsWith('..') || rel.split(sep).includes('..'))
        fail(`path escapes its root: ${path}`);
    return full;
}

function cloneRepo(repo, ref, workDir) {
    const target = mkdtempSync(join(workDir, 'repo-'));
    const args = ['clone', '--quiet', '--depth', '1'];
    if (ref) args.push('--branch', ref);
    args.push(`https://github.com/${repo}.git`, target);
    execFileSync('git', args, { stdio: ['ignore', 'ignore', 'inherit'] });
    return target;
}

function install(kind, entry, checkout) {
    const source = insideOf(checkout, entry.path);
    if (!existsSync(source))
        fail(
            `${kind} "${entry.name}": ${entry.path} not found in ${entry.repo}`,
        );

    if (kind === 'skill') {
        if (
            !statSync(source).isDirectory() ||
            !existsSync(join(source, 'SKILL.md'))
        ) {
            fail(
                `skill "${entry.name}": ${entry.path} must be a directory with SKILL.md`,
            );
        }
        const target = join(CLAUDE_DIR, 'skills', entry.name);
        rmSync(target, { recursive: true, force: true });
        cpSync(source, target, { recursive: true });
        return target;
    }

    if (!statSync(source).isFile() || !source.endsWith('.md')) {
        fail(`agent "${entry.name}": ${entry.path} must be a .md file`);
    }
    const target = join(CLAUDE_DIR, 'agents', `${entry.name}.md`);
    mkdirSync(dirname(target), { recursive: true });
    cpSync(source, target);
    return target;
}

function main() {
    const manifest = JSON.parse(readFileSync(MANIFEST, 'utf8'));
    const entries = [
        ...(manifest.skills ?? []).map((entry) => ({ kind: 'skill', entry })),
        ...(manifest.agents ?? []).map((entry) => ({ kind: 'agent', entry })),
    ];
    entries.forEach(({ kind, entry }) => validateEntry(kind, entry));

    if (entries.length === 0) {
        console.log('Nothing to install: scripts/claude-setup.json is empty.');
        return;
    }

    const workDir = mkdtempSync(join(tmpdir(), 'claude-setup-'));
    const checkouts = new Map();
    try {
        for (const { kind, entry } of entries) {
            const key = `${entry.repo}@${entry.ref ?? ''}`;
            if (!checkouts.has(key))
                checkouts.set(key, cloneRepo(entry.repo, entry.ref, workDir));
            const target = install(kind, entry, checkouts.get(key));
            console.log(`✔ ${kind} ${entry.name} → ${relative(ROOT, target)}`);
        }
    } finally {
        rmSync(workDir, { recursive: true, force: true });
    }
}

main();
