!(function (t) {
  "function" == typeof define && define.amd ? define(t) : t();
})(function () {
  function t(t) {
    let { PI: e, min: r, max: n, cos: s, round: a } = Math,
      i = t[0] | (t[1] << 8) | (t[2] << 16),
      o = t[3] | (t[4] << 8),
      h = (63 & i) / 63,
      u = ((i >> 6) & 63) / 31.5 - 1,
      d = ((i >> 12) & 63) / 31.5 - 1,
      c = ((i >> 18) & 31) / 31,
      l = i >> 23,
      g = ((o >> 3) & 63) / 63,
      f = ((o >> 9) & 63) / 63,
      m = o >> 15,
      b = n(3, m ? (l ? 5 : 7) : 7 & o),
      w = n(3, m ? 7 & o : l ? 5 : 7),
      v = l ? (15 & t[5]) / 15 : 1,
      y = (t[5] >> 4) / 15,
      p = l ? 6 : 5,
      C = 0,
      A = (e, r, n) => {
        let s = [];
        for (let a = 0; a < r; a++)
          for (let i = a ? 0 : 1; i * r < e * (r - a); i++)
            s.push(
              (((t[p + (C >> 1)] >> ((1 & C++) << 2)) & 15) / 7.5 - 1) * n,
            );
        return s;
      },
      M = A(b, w, c),
      I = A(3, 3, 1.25 * g),
      E = A(3, 3, 1.25 * f),
      k = l && A(5, 5, y),
      x = (function (t) {
        let e = t[3],
          r = 128 & t[2],
          n = 128 & t[4];
        return (n ? (r ? 5 : 7) : 7 & e) / (n ? 7 & e : r ? 5 : 7);
      })(t),
      H = a(x > 1 ? 32 : 32 * x),
      S = a(x > 1 ? 32 / x : 32),
      L = new Uint8Array(H * S * 4),
      T = [],
      $ = [];
    for (let t = 0, a = 0; t < S; t++)
      for (let i = 0; i < H; i++, a += 4) {
        let o = h,
          c = u,
          g = d,
          f = v;
        for (let t = 0, r = n(b, l ? 5 : 3); t < r; t++)
          T[t] = s((e / H) * (i + 0.5) * t);
        for (let r = 0, a = n(w, l ? 5 : 3); r < a; r++)
          $[r] = s((e / S) * (t + 0.5) * r);
        for (let t = 0, e = 0; t < w; t++)
          for (let r = t ? 0 : 1, n = 2 * $[t]; r * w < b * (w - t); r++, e++)
            o += M[e] * T[r] * n;
        for (let t = 0, e = 0; t < 3; t++)
          for (let r = t ? 0 : 1, n = 2 * $[t]; r < 3 - t; r++, e++) {
            let t = T[r] * n;
            (c += I[e] * t), (g += E[e] * t);
          }
        if (l)
          for (let t = 0, e = 0; t < 5; t++)
            for (let r = t ? 0 : 1, n = 2 * $[t]; r < 5 - t; r++, e++)
              f += k[e] * T[r] * n;
        let m = o - (2 / 3) * c,
          y = (3 * o - m + g) / 2,
          p = y - g;
        (L[a] = n(0, 255 * r(1, y))),
          (L[a + 1] = n(0, 255 * r(1, p))),
          (L[a + 2] = n(0, 255 * r(1, m))),
          (L[a + 3] = n(0, 255 * r(1, f)));
      }
    return { w: H, h: S, rgba: L };
  }
  function e(t) {
    return Uint8Array.from(atob(t), (t) => t.charCodeAt(0));
  }
  let r;
  const n = (t, e) => {
    t.forEach((t) => {
      let { isIntersecting: e, target: r } = t;
      if (!e) return;
      const n = r;
      a(n), n.render();
    });
  };
  function s(t) {
    null != window?.IntersectionObserver
      ? ((r ??= new IntersectionObserver(n, {
          rootMargin: "100% 100% 100% 100%",
        })),
        r.observe(t))
      : t.render();
  }
  function a(t) {
    r?.unobserve(t);
  }
  class i extends HTMLElement {
    constructor() {
      super(),
        (this.shadow = void 0),
        (this.currentHash = void 0),
        (this.currentStrategy = void 0),
        (this.shadow = this.attachShadow({ mode: "open" }));
    }
    static init() {
      window.customElements.get("thumb-hash") ||
        window.customElements.define("thumb-hash", i);
    }
    static get observedAttributes() {
      return ["value", "strategy"];
    }
    get value() {
      return (this.getAttribute("value") || "").trim();
    }
    set value(t) {
      this.setAttribute("value", t);
    }
    get strategy() {
      const t = (this.getAttribute("strategy") || "").trim().toLowerCase();
      return "img" === t || "average" === t ? t : "canvas";
    }
    set strategy(t) {
      this.setAttribute("strategy", t);
    }
    attributeChangedCallback(t) {
      ["value", "strategy"].includes(t) && s(this);
    }
    connectedCallback() {
      this.setAttribute("aria-hidden", "true"), s(this);
    }
    disconnectedCallback() {
      a(this);
    }
    render() {
      const { value: t, strategy: e, shadow: r } = this;
      if (
        this.needsRender(t, e) &&
        ((this.currentHash = t),
        (this.currentStrategy = e),
        (r.innerHTML = ""),
        t)
      )
        switch (e) {
          case "img":
            this.renderImage(t);
            break;
          case "average":
            this.renderAverage(t);
            break;
          default:
            this.renderCanvas(t);
        }
    }
    needsRender(t, e) {
      return (
        !this.shadow.innerHTML.trim() ||
        t !== this.currentHash ||
        e !== this.currentStrategy
      );
    }
    renderCanvas(r) {
      const {
          width: n,
          height: s,
          pixels: a,
        } = (function (r) {
          const { w: n, h: s, rgba: a } = t(e(r));
          return { width: n, height: s, pixels: a };
        })(r),
        i = document.createElement("canvas");
      (i.style.width = "100%"), (i.style.height = "100%");
      const o = i.getContext("2d");
      if (!o) return;
      (i.width = n), (i.height = s);
      const h = o.createImageData(n, s);
      h.data.set(a), o.putImageData(h, 0, 0), this.shadow.appendChild(i);
    }
    renderAverage(t) {
      const r = (function (t) {
          const {
            r: r,
            g: n,
            b: s,
          } = (function (t) {
            let { min: e, max: r } = Math,
              n = t[0] | (t[1] << 8) | (t[2] << 16),
              s = (63 & n) / 63,
              a = ((n >> 12) & 63) / 31.5 - 1,
              i = n >> 23 ? (15 & t[5]) / 15 : 1,
              o = s - (2 / 3) * (((n >> 6) & 63) / 31.5 - 1),
              h = (3 * s - o + a) / 2,
              u = h - a;
            return {
              r: r(0, e(1, h)),
              g: r(0, e(1, u)),
              b: r(0, e(1, o)),
              a: i,
            };
          })(e(t));
          return `rgb(${Math.round(255 * r)} ${Math.round(255 * n)} ${Math.round(255 * s)})`;
        })(t),
        n = document.createElement("div");
      (n.style.width = "100%"),
        (n.style.height = "100%"),
        (n.style.background = r),
        this.shadow.appendChild(n);
    }
    renderImage(r) {
      const n = document.createElement("img");
      (n.style.width = "100%"),
        (n.style.height = "100%"),
        (n.alt = ""),
        (n.src = (function (r) {
          return (function (e) {
            let r = t(e);
            return (function (t, e, r) {
              let n = 4 * t + 1,
                s = 6 + e * (5 + n),
                a = [
                  137,
                  80,
                  78,
                  71,
                  13,
                  10,
                  26,
                  10,
                  0,
                  0,
                  0,
                  13,
                  73,
                  72,
                  68,
                  82,
                  0,
                  0,
                  t >> 8,
                  255 & t,
                  0,
                  0,
                  e >> 8,
                  255 & e,
                  8,
                  6,
                  0,
                  0,
                  0,
                  0,
                  0,
                  0,
                  0,
                  s >>> 24,
                  (s >> 16) & 255,
                  (s >> 8) & 255,
                  255 & s,
                  73,
                  68,
                  65,
                  84,
                  120,
                  1,
                ],
                i = [
                  0, 498536548, 997073096, 651767980, 1994146192, 1802195444,
                  1303535960, 1342533948, -306674912, -267414716, -690576408,
                  -882789492, -1687895376, -2032938284, -1609899400,
                  -1111625188,
                ],
                o = 1,
                h = 0;
              for (let t = 0, s = 0, i = n - 1; t < e; t++, i += n - 1)
                for (
                  a.push(
                    t + 1 < e ? 0 : 1,
                    255 & n,
                    n >> 8,
                    255 & ~n,
                    (n >> 8) ^ 255,
                    0,
                  ),
                    h = (h + o) % 65521;
                  s < i;
                  s++
                ) {
                  let t = 255 & r[s];
                  a.push(t), (o = (o + t) % 65521), (h = (h + o) % 65521);
                }
              a.push(
                h >> 8,
                255 & h,
                o >> 8,
                255 & o,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                73,
                69,
                78,
                68,
                174,
                66,
                96,
                130,
              );
              for (let [t, e] of [
                [12, 29],
                [37, 41 + s],
              ]) {
                let r = -1;
                for (let n = t; n < e; n++)
                  (r ^= a[n]),
                    (r = (r >>> 4) ^ i[15 & r]),
                    (r = (r >>> 4) ^ i[15 & r]);
                (r = ~r),
                  (a[e++] = r >>> 24),
                  (a[e++] = (r >> 16) & 255),
                  (a[e++] = (r >> 8) & 255),
                  (a[e++] = 255 & r);
              }
              return "data:image/png;base64," + btoa(String.fromCharCode(...a));
            })(r.w, r.h, r.rgba);
          })(e(r));
        })(r)),
        this.shadow.appendChild(n);
    }
  }
  i.init();
});
//# sourceMappingURL=index.umd.js.map
