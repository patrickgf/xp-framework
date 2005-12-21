/* This class is part of the XP framework's EAS connectivity
 *
 * $Id$
 */

package net.xp_framework.easc.server;

import java.util.HashMap;
import javax.naming.NameNotFoundException;

import net.xp_framework.easc.server.ServerContext;
import net.xp_framework.easc.server.Delegate;
import net.xp_framework.easc.reflect.DescriptionList;

/**
 * Server context for the invocation server (EASC mbean)
 *
 */
public class ReflectionServerContext extends ServerContext {
    public DescriptionList descriptions;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   net.xp_framework.easc.reflect.DescriptionList descriptions
     */
    public ReflectionServerContext(DescriptionList descriptions) {
        this.descriptions= descriptions;
    }

    /**
     * Retrieve lookup delegate
     * 
     * @access  public
     * @param   java.lang.String jndiName
     * @return  net.xp_framework.easc.server.Delegate
     */
    public Delegate lookup(final String jndiName) {
        return new Delegate() {
            public Object invoke(ServerContext ctx) throws Exception {
                if ("Services".equals(jndiName)) {
                    return ((ReflectionServerContext)ctx).descriptions;
                }
                
                throw new NameNotFoundException("Name " + jndiName + " not bound");
            }
        };
    }
}
