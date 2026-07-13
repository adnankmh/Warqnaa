from __future__ import annotations
from collections import deque
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageOps
import math

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'flutter_app' / 'assets' / 'images' / 'v02'
CHEST_OUT = OUT / 'prize_boxes'
TICKET_OUT = OUT / 'tickets'
REWARD_OUT = OUT / 'rewards'
for d in (CHEST_OUT, TICKET_OUT, REWARD_OUT):
    d.mkdir(parents=True, exist_ok=True)

SOURCES = {
    'crimson_lion': Path('/mnt/data/regal_crimson_and_gold_treasure_chest.png'),
    'emerald_eagle': Path('/mnt/data/ornate_jeweled_treasure_chest.png'),
    'bronze_dragon': Path('/mnt/data/ornate_treasure_chest_with_glowing_gems.png'),
    'obsidian': Path('/mnt/data/luxurious_gold_trimmed_treasure_chest.png'),
    'royal_amethyst': Path('/mnt/data/royal_treasure_chest_with_gold_accents.png'),
    'diamond_phoenix': Path('/mnt/data/luxurious_golden_treasure_chest_with_gems.png'),
}


def remove_light_background(im: Image.Image) -> Image.Image:
    rgb = im.convert('RGB')
    w, h = rgb.size
    pix = rgb.load()
    eligible = bytearray(w*h)
    for y in range(h):
        off = y*w
        for x in range(w):
            r,g,b = pix[x,y]
            mx, mn = max(r,g,b), min(r,g,b)
            # Generated checker/white background. Objects can contain white, so
            # only edge-connected pixels are removed below.
            if mn >= 218 and mx-mn <= 24:
                eligible[off+x] = 1
    bg = bytearray(w*h)
    q: deque[tuple[int,int]] = deque()
    for x in range(w):
        for y in (0,h-1):
            idx=y*w+x
            if eligible[idx] and not bg[idx]: bg[idx]=1; q.append((x,y))
    for y in range(h):
        for x in (0,w-1):
            idx=y*w+x
            if eligible[idx] and not bg[idx]: bg[idx]=1; q.append((x,y))
    while q:
        x,y=q.popleft()
        for nx,ny in ((x-1,y),(x+1,y),(x,y-1),(x,y+1)):
            if 0<=nx<w and 0<=ny<h:
                idx=ny*w+nx
                if eligible[idx] and not bg[idx]:
                    bg[idx]=1; q.append((nx,ny))
    alpha=Image.new('L',(w,h),255)
    ap=alpha.load()
    for y in range(h):
        for x in range(w):
            if bg[y*w+x]: ap[x,y]=0
    # Slight feather removes the baked white halo without touching object whites.
    alpha=alpha.filter(ImageFilter.GaussianBlur(0.7))
    rgba=rgb.convert('RGBA')
    rgba.putalpha(alpha)
    return rgba


def crop_alpha(im: Image.Image, pad: int = 28) -> Image.Image:
    a=im.getchannel('A')
    box=a.getbbox()
    if not box: return im
    l,t,r,b=box
    return im.crop((max(0,l-pad),max(0,t-pad),min(im.width,r+pad),min(im.height,b+pad)))


def fit_canvas(im: Image.Image, size=(1200,900), margin=50) -> Image.Image:
    canvas=Image.new('RGBA',size,(0,0,0,0))
    target=(size[0]-2*margin,size[1]-2*margin)
    item=ImageOps.contain(im,target,Image.Resampling.LANCZOS)
    x=(size[0]-item.width)//2
    y=(size[1]-item.height)//2
    canvas.alpha_composite(item,(x,y))
    return canvas

# Chests: isolated, centered, consistent front-oriented presentation.
for key, src in SOURCES.items():
    raw=remove_light_background(Image.open(src))
    raw=crop_alpha(raw,18)
    # Remove most side-perspective whitespace and enlarge front face.
    l=int(raw.width*0.08); r=int(raw.width*0.98)
    raw=raw.crop((l,0,r,raw.height))
    full=fit_canvas(raw,(1200,900),48)
    full.save(CHEST_OUT/f'{key}.png', optimize=True)
    # Separate layers used by Flutter's front-opening animation.
    split=int(full.height*0.43)
    lid=Image.new('RGBA',full.size,(0,0,0,0)); lid.alpha_composite(full.crop((0,0,full.width,split)),(0,0))
    body=Image.new('RGBA',full.size,(0,0,0,0)); body.alpha_composite(full.crop((0,split,full.width,full.height)),(0,split))
    panel=Image.new('RGBA',full.size,(0,0,0,0))
    panel_y=int(full.height*0.47)
    panel.alpha_composite(full.crop((int(full.width*.18),panel_y,int(full.width*.91),int(full.height*.88))),(int(full.width*.18),panel_y))
    lid.save(CHEST_OUT/f'{key}_lid.png', optimize=True)
    body.save(CHEST_OUT/f'{key}_body.png', optimize=True)
    panel.save(CHEST_OUT/f'{key}_front_panel.png', optimize=True)

# Transparent ticket base.
ticket_src=remove_light_background(Image.open('/mnt/data/luxurious_golden_ticket_with_royal_embellishments.png'))
ticket_src=crop_alpha(ticket_src,12)
font_path='/usr/share/fonts/truetype/noto/NotoSans-Black.ttf'
values=[50,100,200,500,1000,2000,4000,5000,8000,10000,20000,30000,50000,100000]
for value in values:
    canvas=fit_canvas(ticket_src,(1400,880),18)
    draw=ImageDraw.Draw(canvas)
    label=f'{value:,}'
    font_size=250 if len(label)<=4 else 205 if len(label)<=6 else 160
    font=ImageFont.truetype(font_path,font_size)
    bbox=draw.textbbox((0,0),label,font=font,stroke_width=5)
    tw,th=bbox[2]-bbox[0],bbox[3]-bbox[1]
    x=(canvas.width-tw)//2
    y=(canvas.height-th)//2-12
    # black/gold glow and crisp ivory face
    glow=Image.new('RGBA',canvas.size,(0,0,0,0)); gd=ImageDraw.Draw(glow)
    gd.text((x,y),label,font=font,fill=(255,195,45,255),stroke_width=14,stroke_fill=(60,12,8,240))
    glow=glow.filter(ImageFilter.GaussianBlur(10))
    canvas=Image.alpha_composite(canvas,glow)
    draw=ImageDraw.Draw(canvas)
    draw.text((x,y),label,font=font,fill=(255,248,214,255),stroke_width=6,stroke_fill=(126,57,5,255))
    canvas.save(TICKET_OUT/f'ticket_{value}.png',optimize=True)

# Use the exact original red Pasha asset as supplied by the project.
pasha=Image.open(ROOT/'flutter_app/assets/images/pasha.png').convert('RGBA')
if pasha.getchannel('A').getextrema()==(255,255):
    # If older build stored black as background, remove edge-connected near-black only.
    rgb=pasha.convert('RGB'); w,h=rgb.size; eligible=bytearray(w*h)
    for y in range(h):
        for x in range(w):
            r,g,b=rgb.getpixel((x,y));
            if max(r,g,b)<16: eligible[y*w+x]=1
    bg=bytearray(w*h); q=deque()
    for x in range(w):
        for y in (0,h-1):
            i=y*w+x
            if eligible[i] and not bg[i]: bg[i]=1;q.append((x,y))
    for y in range(h):
        for x in (0,w-1):
            i=y*w+x
            if eligible[i] and not bg[i]: bg[i]=1;q.append((x,y))
    while q:
        x,y=q.popleft()
        for nx,ny in ((x-1,y),(x+1,y),(x,y-1),(x,y+1)):
            if 0<=nx<w and 0<=ny<h:
                i=ny*w+nx
                if eligible[i] and not bg[i]: bg[i]=1;q.append((nx,ny))
    a=Image.new('L',(w,h),255); ap=a.load()
    for y in range(h):
        for x in range(w):
            if bg[y*w+x]: ap[x,y]=0
    pasha.putalpha(a)
pasha=fit_canvas(crop_alpha(pasha,15),(900,900),60)
pasha.save(REWARD_OUT/'pasha_day.png',optimize=True)

# Reward icons made as real assets (not emoji-only) for the reveal animation.
def icon_canvas(): return Image.new('RGBA',(900,900),(0,0,0,0))
def rounded_gradient(colors, border=(255,205,74,255)):
    im=icon_canvas(); px=im.load()
    for y in range(130,770):
        t=(y-130)/640
        c=tuple(round(colors[0][i]*(1-t)+colors[1][i]*t) for i in range(3))
        for x in range(130,770):
            if ((x-450)/320)**2+((y-450)/320)**2 <=1: px[x,y]=(*c,255)
    d=ImageDraw.Draw(im); d.ellipse((125,125,775,775),outline=border,width=28)
    return im

# Writing color
im=rounded_gradient(((21,31,55),(23,179,213))); d=ImageDraw.Draw(im)
f=ImageFont.truetype(font_path,360); d.text((450,430),'A',font=f,anchor='mm',fill=(255,255,255),stroke_width=8,stroke_fill=(4,18,35)); im.save(REWARD_OUT/'writing_color.png',optimize=True)
# Player color / avatar halo
im=rounded_gradient(((90,30,150),(245,80,145))); d=ImageDraw.Draw(im); d.ellipse((285,240,615,570),fill=(22,28,43),outline=(255,255,255),width=18); d.ellipse((340,305,560,525),fill=(246,193,57)); d.arc((250,205,650,655),10,350,fill=(255,230,125),width=36); im.save(REWARD_OUT/'player_color.png',optimize=True)
# Personal cover
im=Image.new('RGBA',(900,900),(0,0,0,0)); d=ImageDraw.Draw(im); d.rounded_rectangle((150,210,750,690),radius=60,fill=(32,10,55),outline=(255,200,75),width=32); d.rounded_rectangle((205,265,695,635),radius=42,fill=(98,35,150),outline=(255,238,170),width=12); d.polygon([(450,300),(495,400),(610,410),(520,480),(550,590),(450,530),(350,590),(380,480),(290,410),(405,400)],fill=(255,210,70)); im.save(REWARD_OUT/'profile_cover.png',optimize=True)
# Tokens
im=icon_canvas(); d=ImageDraw.Draw(im)
for i,(x,y) in enumerate([(300,520),(450,555),(570,490),(400,400),(535,360)]):
    d.ellipse((x-120,y-58,x+120,y+58),fill=(226,151,25),outline=(255,234,128),width=18); d.ellipse((x-70,y-38,x+70,y+38),fill=(255,196,48),outline=(156,90,5),width=8); d.text((x,y),'W',font=ImageFont.truetype(font_path,62),anchor='mm',fill=(111,56,2))
im.save(REWARD_OUT/'tokens.png',optimize=True)
# Ticket 200 reward duplicate for stable path
Image.open(TICKET_OUT/'ticket_200.png').save(REWARD_OUT/'ticket_200.png',optimize=True)

print(f'Generated V0.2 assets in {OUT}')
