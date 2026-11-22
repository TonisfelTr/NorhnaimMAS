#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: bash telemedicine/scripts/create_mobile_certs.sh 192.168.1.50" >&2
    exit 2
fi
LAN_IP="$1"
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd -- "$SCRIPT_DIR/../.." && pwd)"
CERT_DIR="$ROOT/telemedicine/certs"
CA_KEY="$CERT_DIR/rootCA.key"
CA_CRT="$CERT_DIR/rootCA.crt"
LEAF_KEY="$CERT_DIR/mobile.key"
LEAF_CRT="$CERT_DIR/mobile.crt"

command -v openssl >/dev/null 2>&1 || { echo 'OpenSSL is required.' >&2; exit 1; }
mkdir -p "$CERT_DIR"
chmod 700 "$CERT_DIR"
umask 077

if [[ -f "$CA_KEY" && -f "$CA_CRT" ]]; then
    : # Reuse trusted local CA for another LAN IP.
elif [[ -e "$CA_KEY" || -e "$CA_CRT" ]]; then
    echo 'Incomplete root CA pair. Recover rootCA.key/rootCA.crt rather than replacing trust.' >&2
    exit 1
else
    echo 'Creating LOCAL development CA (do not use for production).'
    openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:3072 -out "$CA_KEY" >/dev/null 2>&1
    openssl req -x509 -new -sha256 -days 3650 \
      -key "$CA_KEY" -out "$CA_CRT" \
      -subj '/CN=NORHNAIM WebRTC Local Dev CA' \
      -addext 'basicConstraints=critical,CA:TRUE,pathlen:0' \
      -addext 'keyUsage=critical,keyCertSign,cRLSign' >/dev/null 2>&1
    chmod 600 "$CA_KEY"
    chmod 644 "$CA_CRT"
fi

# Verify the exact IP SAN entry. Some OpenSSL builds print a mismatch from
# `-checkip` but still return exit code 0, so its status alone is insufficient.
if [[ -f "$LEAF_CRT" && -f "$LEAF_KEY" ]] && \
    openssl x509 -in "$LEAF_CRT" -noout -ext subjectAltName 2>/dev/null \
      | tr ',' '\\n' \
      | sed 's/^[[:space:]]*//; s/[[:space:]]*$//' \
      | grep -Fqx "IP Address:$LAN_IP" && \
    openssl x509 -in "$LEAF_CRT" -noout -checkend 86400 >/dev/null 2>&1; then
    echo "Existing HTTPS certificate already includes LAN IP $LAN_IP."
    exit 0
fi

TMP_DIR="$(mktemp -d "$CERT_DIR/.tls-XXXXXX")"
trap 'rm -rf -- "$TMP_DIR"' EXIT
cat > "$TMP_DIR/server.ext" <<EOF
basicConstraints=critical,CA:FALSE
keyUsage=critical,digitalSignature,keyEncipherment
extendedKeyUsage=serverAuth
subjectAltName=IP:$LAN_IP,IP:127.0.0.1,DNS:localhost
EOF

openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 \
  -out "$TMP_DIR/mobile.key" >/dev/null 2>&1
openssl req -new -sha256 \
  -key "$TMP_DIR/mobile.key" \
  -subj "/CN=$LAN_IP" \
  -out "$TMP_DIR/mobile.csr" >/dev/null 2>&1
openssl x509 -req -sha256 -days 120 \
  -in "$TMP_DIR/mobile.csr" \
  -CA "$CA_CRT" -CAkey "$CA_KEY" -CAcreateserial \
  -out "$TMP_DIR/mobile.crt" -extfile "$TMP_DIR/server.ext" >/dev/null 2>&1
chmod 600 "$TMP_DIR/mobile.key"
chmod 644 "$TMP_DIR/mobile.crt"
mv "$TMP_DIR/mobile.key" "$LEAF_KEY"
mv "$TMP_DIR/mobile.crt" "$LEAF_CRT"

# Ensure certs/ stays out of commits, including the private CA key.
[[ -f "$ROOT/.gitignore" ]] || touch "$ROOT/.gitignore"
if ! grep -qxF '/telemedicine/certs/' "$ROOT/.gitignore"; then
    printf '\n# Local-only WebRTC TLS certificate keys\n/telemedicine/certs/\n' >> "$ROOT/.gitignore"
fi
printf '\nTLS certificate for %s generated.\n' "$LAN_IP"
printf 'PHONE: install and TRUST %s on the phone.\n' "$CA_CRT"
printf 'NEVER share %s (CA signing private key).\n' "$CA_KEY"
