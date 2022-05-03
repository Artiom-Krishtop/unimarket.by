{"version":3,"sources":["lazyload.js"],"names":["isNative","HTMLImageElement","prototype","BX","images","document","querySelectorAll","forEach","img","src","dataset","removeAttribute","srcset","undefined","observerOptions","rootMargin","documentElement","observer","IntersectionObserver","onIntersection","addCustomEvent","event","observe","block","entries","entry","isIntersecting","observableImages","slice","call","target","origSrc","data","origSrcset","create","attrs","events","load","adjust","data-lazy-src","data-src","data-srcset","remove","this","Landing","Event","Block","node","onCustomEvent","addEventListener","observableBg","bg","origBg","origStyle","origSrc2x","newBgStyle","getAttribute","origBgStyle","split","bgVal","push","join","style","setProperty","data-style","data-src2x","fffffffffu","observableStyles","styleNode","unobserve"],"mappings":"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","file":"lazyload.map.js"}