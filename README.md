# Everlights
Control the Everlights permanent Christmas lights with this little PHP app. I was going to split it out into the class and the app, but honestly, I'm just too lazy. It's a tiny program, and I wanted to be able to move it around easily.

I'll gladly accept pull requests if someone feels the urge to separate out the class, app, and config.

## Usage
It accepts one argument, which is either "off" or one of the defined sequences.

## Protocol

The app talks to the control box on UDP port 8080. It sends one character commands (all uppercase letters) with parameters afterward. The parameters are sent as 2 hex characters (in ASCII). For example, the number 1 is always sent as 01. I don't know why they don't send them as single binaries.

### Commands

- **O**: O01 turns on, and O00 turns off
- **P**: Choose a pattern. 
  - The patterns are as follows:
    1. **00** No pattern
    1. **01** Blink
    1. **02** Chase left
    1. **03** Chase right
    1. **04** Fade
    1. **05** Strobe
    1. **06** Twinkle
	1. **07** Random
  - You can choose multiple patterns, just send separate commands for them. Sending 00 will always reset it to no pattern.
  - To turn off one pattern (when you have multiple patterns going), send it with 8 instead of 0 as the first character.
- **S**: Pattern speed, from 00 to ff
- **I**: Pattern brightness (intensity?), from 00 to ff
- **U**: Change one light in the sequence. Takes two parameters, first the light number (00 to ff) in the sequence, then a 6-character color (in GGRRBB hex format)
- **C**: Change the whole sequence. First parameter is the length, from 00 to ff. This is followed by 6-character colors for each light in the sequence.

### Notes
- For some reason, the colors are given in GRB order instead of the standard RGB
- The blue lights are way brighter than the red and green, so to get a decent white, the blue needs to be about a third of the red and green values

