#!/usr/bin/env python3
"""Generate JSON from CSV, for the certificates system."""

import csv
import sys
import json
import hashlib


def fp(nome, email, local, ano, mes, dia):
    """Compute fingerprint."""
    m = ["", "Janeiro", "Fevereiro", "Março", "Abril",
         "Maio", "Junho", "Julho", "Agosto", "Setembro",
         "Outubro", "Novembro", "Dezembro"]
    data = "%s de %s de %s" % (dia, m[int(mes)], ano)

    oldfinger = (['nh', 'santacruz', 'pelotas', 'poa'], '2017')

    if (local in oldfinger[0]) and (ano == oldfinger[1]):
        data = nome + email + local + dia
    else:
        data = nome + email + local + data

    return hashlib.md5(data.encode('utf-8')).hexdigest()


if len(sys.argv) != 5:
    print("""usage:\n
             \tgera_lista_participantes.py config csv_participantes\n""")
    sys.exit(1)

config = json.load(sys.argv[1])
filename = sys.argv[2]
horas = config.get('horas', 5)
instituicao = config['instituicao']
cidade = config['cidade']
horas_organizacao = config.get('horas_organizacao', horas)

ano, mes, dia, local, *_ = filename.split('-')
ano = ano.split('/')[-1]
local = local.split('.')[0]

with open(filename) as csvfile:
    reader = csv.reader(csvfile)
    participantes = [{'nome': row[0],
                      'email': row[1],
                      'fingerprint': fp(row[0], row[1], local, ano, mes, dia),
                      'role': row[3] if len(row) > 3 else "participante"}
                     for row in reader
                     if row[2] != '' and row[1].lower()[:4] != "nome"]

evento = {"horas": horas, "instituicao": instituicao,
          "horas_organizacao": horas_organizacao,
          "data": "%s-%s-%s" % (ano, mes, dia), "cidade": cidade,
          "codename": local, "participantes": participantes}

with open("data/%s-%s-%s-%s.json" % (ano, mes, dia, local), "w") as outfile:
    print(json.dumps(evento, indent=4, sort_keys=True), file=outfile)
