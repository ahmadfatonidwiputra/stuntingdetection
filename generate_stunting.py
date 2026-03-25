import urllib.request
import ssl

def fetch(url):
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    response = urllib.request.urlopen(req, context=ctx)
    lines = response.read().decode('utf-8').strip().split('\n')
    data = {}
    headers = lines[0].split('\t')
    for line in lines[1:]:
        parts = line.split('\t')
        if len(parts) > 2:
            month = parts[0]
            data[month] = {
                'SD3neg': float(parts[headers.index('SD3neg')]),
                'SD2neg': float(parts[headers.index('SD2neg')]),
                'SD0'   : float(parts[headers.index('SD0')]),
            }
    return data

try:
    boys = fetch('https://raw.githubusercontent.com/ewheeler/pygrowup/master/pygrowup/tables/lhfa_boys_0_to_5_zscores.txt')
    girls = fetch('https://raw.githubusercontent.com/ewheeler/pygrowup/master/pygrowup/tables/lhfa_girls_0_to_5_zscores.txt')

    php = "<?php\n// WHO Child Growth Standards (Height-for-age)\n// Digunakan sesuai standar Kemenkes RI Permenkes No 2 Tahun 2020\nreturn [\n    'boys' => [\n"
    for m in range(61):
        v = boys[str(m)]
        php += f"        {m} => ['SD3neg' => {v['SD3neg']}, 'SD2neg' => {v['SD2neg']}, 'median' => {v['SD0']}],\n"
    php += "    ],\n    'girls' => [\n"
    for m in range(61):
        v = girls[str(m)]
        php += f"        {m} => ['SD3neg' => {v['SD3neg']}, 'SD2neg' => {v['SD2neg']}, 'median' => {v['SD0']}],\n"
    php += "    ]\n];\n"

    with open('config/stunting.php', 'w') as f:
        f.write(php)
    print('config/stunting.php generated successfully!')
except Exception as e:
    import traceback
    traceback.print_exc()
